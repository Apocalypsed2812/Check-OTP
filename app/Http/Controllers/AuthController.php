<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\OTP;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use Twilio\Rest\Client;
use HTTP_Request2;
// use GuzzleHttp\Client;

class AuthController extends Controller
{
    public function generateOTP(){
        $otp = mt_rand(100000, 999999);
        return $otp;
    }

    public function sendOTP($email, $otp){
        try {
            Mail::to($email)->send(new OTPMail($otp));
            return true; 
        } catch (Exception $e) {
            Log::error("Error sending OTP email: " . $e->getMessage());
            return false; 
        }
    }

    public function login(Request $request){
        // Kiểm tra đầu vào
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
            'password.min' => 'Password must a least 6',
        ]);

        // Kiểm tra username
        $user = Account::where('username', $data['username'])->first();

        // Kiểm tra password
        if($user && Hash::check($data['password'], $user->password)){
            // Nếu account đã được kích hoạt thì mới cho đăng nhập
            if($user->status === 'active'){
                if($request->session()->has('registered')){
                    $request->session()->forget('registered');
                }

                $request->session()->put('login', true);
                $request->session()->put('username', $user->username);

                return redirect('/home');
            }
            else{
                return redirect('/login')->with('error', 'Account is inactive. Please try again');
            }
        }
        else{
            return redirect('/login')->with('error', 'Username or password not correct');
        }
    }

    public function register(Request $request){
        // Kiểm tra đầu vào
        $data = $request->validate([
            'username' => 'required|string|unique:account',
            'password' => 'required|string|min:6',
            'repassword' => 'required|string|min:6|same:password',
            'email' => 'required|email|unique:account',
            'phone' => 'required',
        ], [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
            'repassword.required' => 'Please enter your password confirm.',
            'repassword.same' => 'The password confirm does not match',
            'password.min' => 'Password must a least 6',
            'email.required' => 'Please enter your email.',
            'email.email' => 'Please enter correct email format.',
            'phone.required' => 'Please enter your phone',
        ]);

        // Tạo otp ngãu nhiên với 6 số
        $otp = $this->generateOTP();

        // Tạo account ứng với Account Model
        $account = new Account([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => 'inactive',
        ]);

        // Tạo OTP ứng với OTP Model
        $otpRecord = new OTP([
            'email' => $data['email'],
            'otp' => $otp,
            'status' => 'active',
        ]);
        

        // Lấy ra số điện thoại và thêm +84 vào đầu
        $phoneNumber = substr_replace($data['phone'], '+84', 0, 1);

        // Gửi OTP về email
        // $this->sendOTP($data['email'], $otp);

        // Gửi OTP về số điện thoại tương ứng
        $this->sendSMSNotification($phoneNumber, $otp);

        // Lưu các giá trị account và otp vào database
        $otpRecord->save();
        $account->save();

        // Tạo session đã đăng ký để kiểm tra truy cập vào /otp
        $request->session()->put('registered', true);

        return redirect('/otp')->with(['otp'=> $otp, 'email' => $data['email']]);
    }

    public function logout(Request $request){
        $request->session()->forget('login');
        return redirect('/login');
    }

    public function checkOTP(Request $request){
        $otpValue = $request->input('otp');
        $email = $request->input('email');

        // Lấy ra các giá trị otp và account
        $otpRecord = OTP::where('otp', $otpValue)->first();
        $account = Account::where('email', $email)->first();

        if ($otpRecord) {
            // Kiểm tra trạng thái otp
            if ($otpRecord->status === 'active') {
                // Cập nhật trạng thái otp và account
                $otpRecord->update(['status' => 'inactive']);
                $account->update(['status' => 'active']);

                return redirect('/login')->with('success', 'OTP is valid. You can now log in.');
            } else {
                return redirect()->back()->with(['error' => 'OTP has expired. Please try again.', 'email' => $email])->withErrors(['otp' => 'Invalid OTP']);
            }
        } else {
            return redirect()->back()->with(['error' => 'Invalid OTP. Please try again.', 'email' => $email])->withErrors(['otp' => 'Invalid OTP']);
        }
    }

    public function unactivedOTP(Request $request){
        $otp = $request->input('otp');

        //Lấy ra record ứng với otp trong db
        $otpRecord = OTP::where('otp', $otp)->first();

        // Cập nhật trạng thái của opt từ active -> inactive
        $otpRecord->update(['status' => 'inactive']);

        return response()->json(['otp' => $otp]);
    }

    private function sendOTPWhatsapp($phoneNumber, $otp)
    {
        try{
            // Cấu hình twilio
            $twilioSid = 'ACfb993e5f595f8d2d5990e204ae844874';
            $twilioToken = 'f21c84e58e6ce55736ed639f5e1b811e';
            $twilioPhoneNumber = 'whatsapp:+14155238886';

            $twilio = new Client($twilioSid, $twilioToken);

            // Gửi otp đến whatsapp với twilio
            $twilio->messages->create(
                "whatsapp:" . $phoneNumber, 
                [
                    "from" => $twilioPhoneNumber,
                    "body" => "Your OTP is: " . $otp,
                ]
            );
            return true;
        }
        catch (Exception $e){
            // echo 'Error sending OTP: ' . $e->getMessage();
            return false;
        }
    }

    private function sendSMSNotification($phoneNumber, $otp)
    {
        // Cấu hình twilio
        $twilioSid = 'ACfb993e5f595f8d2d5990e204ae844874';
        $twilioToken = 'f21c84e58e6ce55736ed639f5e1b811e';
        $twilioPhoneNumber = '+17039978266';

        $twilio = new Client($twilioSid, $twilioToken);

        // Gửi tin nhắn sms với twilio
        $twilio->messages->create(
            $phoneNumber, 
            [
                "from" => $twilioPhoneNumber,
                "body" => "Your OTP is: {$otp}",
            ]
        );
    }

    public function sendViberOTP($phoneNumber, $otp){
        $viberApiKey = '5223eb46ba67e4a6-f1f4e36466092046-66bd3ecbf2759b7e';
        $viberBotName = 'Apocalysed';

        $client = new Client();
        $response = $client->post('https://chatapi.viber.com/pa/send_message', [
            'headers' => [
                'X-Viber-Auth-Token' => $viberApiKey,
            ],
            'json' => [
                'receiver' => $phoneNumber,
                'type' => 'text',
                'text' => "Your OTP code is: $otp",
                'sender' => [
                    'name' => $viberBotName,
                ],
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $responseData = json_decode($response->getBody(), true);
            $status = $responseData['status'];
            if ($responseData['status'] == 0) {
                // Thành công: Xử lý tiếp theo
                return response()->json(['message' => 'OTP sent successfully']);
            } else {
                // Thất bại: Xử lý lỗi
                return response()->json(['error' => "Failed to send OTP with $status"], 500);
            }
        } else {
            // Lỗi HTTP: Xử lý lỗi
            return response()->json(['error' => 'HTTP error'], $statusCode);
        }
    }

    public function sendOTPWithWhatsappAPI($phone, $otp) {
        $phoneNumber = substr_replace($phone, '84', 0, 1);
        $request = new HTTP_Request2();
        $request->setUrl('https://e1gzj2.api.infobip.com/whatsapp/1/message/template');
        $request->setMethod(HTTP_Request2::METHOD_POST);
        $request->setConfig(array(
            'follow_redirects' => TRUE
        ));
        $request->setHeader(array(
            'Authorization' => 'App c1b048b84c903b12ad8d9eda81cbe1a5-f39056f8-fbe0-4064-bee8-9d6aeac5b59c',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ));
    
        // Thay đổi số điện thoại và nội dung tin nhắn tại đây
        $requestBody = '{
            "messages": [
                {
                    "from": "447860099299",
                    "to": "'.$phoneNumber.'",
                    "messageId": "c1fccd52-e32f-4dd4-b36a-b90f5cae0f07",
                    "content": {
                        "templateName": "welcome_multiple_languages",
                        "templateData": {
                            "body": {
                                "placeholders": ["'.$otp.'"]
                            }
                        },
                        "language": "en"
                    }
                }
            ]
        }';
    
        $request->setBody($requestBody);
    
        try {
            $response = $request->send();
            if ($response->getStatus() == 200) {
                echo $response->getBody();
            } else {
                echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase();
            }
        } catch (HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    
}
