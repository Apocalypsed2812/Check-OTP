<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function renderHome(){
        $products = Product::All();
        return view('home', ['products' => $products]);
    }

    public function addProduct(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer|min:0',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'name.required' => 'Please enter product name.',
            'price.required' => 'Please enter product price.',
            'description.required' => 'Please enter product description',
            'quantity.reuqired' => 'Please enter product quantity',
        ]);

        $product = new Product([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
        ]);

        if($request->hasFile('image')){
            $imagePath = $request->file('image')->store('image', 'public');
            $data['image'] = $imagePath;
        }

        $product = new Product($data);
        $product->save();

        return redirect()->back()->with("add-success", "Product added successfully");
    }

    public function deleteProduct(Request $request){
        $id = $request->input('id-delete');

        $product = Product::find($id);
        if($product){
            $product->delete();
        }
        return redirect()->back()->with("delete-success", "Product added successfully");
    }

    public function editProduct(Request $request){
        $id = $request->input('id-edit');
        $name = $request->input('name-edit');
        $price = $request->input('price-edit');
        $description = $request->input('description-edit');
        $quantity = $request->input('quantity-edit');

        $product = Product::find($id);

        if($product){
            $product->update([
                "name" => $name,
                "price" => $price,
                "description" => $description,
                "quantity" => $quantity,
            ]);
        }

        return redirect()->back()->with("edit-success", "Product added successfully");
    }

}
