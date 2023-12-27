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
        // $this->sendSMSNotification($phoneNumber, $otp);

        // Gửi OTP về account viber
        // $this->setWebHoot();
        // $this->sendViberOTP("+84 783664025", $otp);
        // echo $this->sendViberOTP("+84 783664025", $otp);

        // Gửi OTP về số điện thoại tương ứng
        if(!$this->sendOTPWhatsapp($phoneNumber, $otp)){
            return redirect()->back()->with('error', 'Has Ocurred Error');
        }
        // Lưu các giá trị account và otp vào database
        $otpRecord->save();
        $account->save();

        // Tạo session đã đăng ký để kiểm tra truy cập vào /otp
        $request->session()->put('registered', true);

        return redirect('/otp')->with(['otp'=> $otp, 'email' => $data['email']]);
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
            $twilioSid = 'ACa223b7f4dd8962b29d814d76039900a7';
            $twilioToken = '42fee8b72f0b063e03bd263744f7445b';
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
        $twilioSid = 'ACa223b7f4dd8962b29d814d76039900a7';
        $twilioToken = '42fee8b72f0b063e03bd263744f7445b';
        $twilioPhoneNumber = '+13048400928';

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
}
