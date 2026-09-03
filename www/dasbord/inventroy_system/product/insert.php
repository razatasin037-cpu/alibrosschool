<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">

</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-10">

<div class="bg-white rounded-2xl shadow-lg">

<div class="bg-orange-500 rounded-t-2xl px-8 py-5">

<h2 class="text-3xl text-white font-bold">

<i class="ri-box-3-line"></i>

Add New Product

</h2>

</div>

<form action="insert_product.php" method="POST" enctype="multipart/form-data" class="p-8">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<!-- Product Name -->

<div>

<label class="font-semibold">Product Name</label>

<input type="text" name="product_name"

class="w-full mt-2 border rounded-xl p-3 focus:ring-2 focus:ring-orange-500"

required>

</div>

<!-- Category -->

<div>

<label class="font-semibold">Category</label>

<select name="category_id"

class="w-full mt-2 border rounded-xl p-3">

<option>Select Category</option>

</select>

</div>

<!-- Supplier -->

<div>

<label class="font-semibold">Supplier</label>

<select name="supplier_id"

class="w-full mt-2 border rounded-xl p-3">

<option>Select Supplier</option>

</select>

</div>

<!-- Purchase Price -->

<div>

<label class="font-semibold">Purchase Price</label>

<input type="number" name="purchase_price"

class="w-full mt-2 border rounded-xl p-3">

</div>

<!-- Selling Price -->

<div>

<label class="font-semibold">Selling Price</label>

<input type="number" name="selling_price"

class="w-full mt-2 border rounded-xl p-3">

</div>

<!-- Quantity -->

<div>

<label class="font-semibold">Quantity</label>

<input type="number" name="quantity"

class="w-full mt-2 border rounded-xl p-3">

</div>

<!-- Minimum Stock -->

<div>

<label class="font-semibold">Minimum Stock</label>

<input type="number" name="minimum_stock"

class="w-full mt-2 border rounded-xl p-3">

</div>

<!-- Image -->

<div>

<label class="font-semibold">Product Image</label>

<input type="file" name="image"

class="w-full mt-2 border rounded-xl p-3">

</div>

<!-- Status -->

<div>

<label class="font-semibold">Status</label>

<select name="status"

class="w-full mt-2 border rounded-xl p-3">

<option value="Available">Available</option>

<option value="Unavailable">Unavailable</option>

</select>

</div>

</div>

<!-- Description -->

<div class="mt-6">

<label class="font-semibold">Description</label>

<textarea name="description"

rows="5"

class="w-full mt-2 border rounded-xl p-3"></textarea>

</div>

<div class="mt-8">

<button

class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-semibold">

<i class="ri-save-line"></i>

Save Product

</button>

</div>

</form>

</div>

</div>

</body>

</html>