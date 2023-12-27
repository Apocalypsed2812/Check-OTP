let viewProduct = document.querySelectorAll('.view-product');
viewProduct.forEach(item => {
    item.onclick = () => {
        let name = item.getAttribute("data-name");
        let price = item.getAttribute("data-price");
        let description = item.getAttribute("data-description");
        let quantity = item.getAttribute("data-quantity");
        let imagePath = item.getAttribute("data-image");

        document.getElementById('name-view').value = name;
        document.getElementById('price-view').value = price;
        document.getElementById('description-view').value = description;
        document.getElementById('quantity-view').value = quantity;
        document.getElementById('image-view').src = "/storage/" + imagePath;
    }
})

let deleteProduct = document.querySelectorAll('.delete-product');
deleteProduct.forEach(item => {
    item.onclick = () => {
        let id = item.getAttribute("data-id");
        let name = item.getAttribute("data-name");

        document.getElementById('id-delete').value = id;
        document.getElementById('name-delete').innerHTML = name;
    }
})

let editProduct = document.querySelectorAll('.edit-product');
editProduct.forEach(item => {
    item.onclick = () => {
        let id = item.getAttribute("data-id");
        let name = item.getAttribute("data-name");
        let price = item.getAttribute("data-price");
        let description = item.getAttribute("data-description");
        let quantity = item.getAttribute("data-quantity");

        document.getElementById('id-edit').value = id;
        document.getElementById('name-edit').value = name;
        document.getElementById('price-edit').value = price;
        document.getElementById('description-edit').value = description;
        document.getElementById('quantity-edit').value = quantity;
    }
})