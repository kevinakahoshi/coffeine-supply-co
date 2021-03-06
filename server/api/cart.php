<?php
$link = get_db_link();

if ($request['method'] === 'GET') {
  $sessionSetCheck = isset($_SESSION['cart_id']);
  if (!$sessionSetCheck) {
    $response['body'] = [];
    send($response);
  } else {
    $new_items = "SELECT cartItems.cartItemId
                      AS id, cartItems.productId, cartItems.quantity, products.name, products.price, products.image, products.shortDescription
                    FROM cartItems
                    JOIN products
                      ON cartItems.productId = products.productId
                   WHERE cartItems.cartId={$_SESSION['cart_id']}";
    $total_query = $link->query($new_items);
    $response['body'] = mysqli_fetch_all($total_query, MYSQLI_ASSOC);
    send($response);
  }
}

if ($request['method'] === 'POST') {
  $product_id = $request['body']['productId'];
  $operator = $request['body']['operator'];
  $check_product_id = isset($product_id);
  if (!$check_product_id) {
    throw new ApiError('That is not a valid product ID', 400);
  }
  $select_product = "SELECT *
                       FROM products
                      WHERE productId=$product_id";
  $product_query = $link->query($select_product);
  $product_info = mysqli_fetch_assoc($product_query);
  $product_price = $product_info['price'];
  if (!isset($_SESSION['cart_id'])) {
    $inserted_time = "INSERT INTO carts (createdAt)
                      VALUES (CURRENT_TIMESTAMP)";
    $time_query = $link->query($inserted_time);
    $cart_id = $link->insert_id;
    $cart_items = "INSERT INTO cartItems (cartId, productId, price, quantity)
                        VALUES ($cart_id, $product_id, $product_price, 1)
                            ON DUPLICATE
                    KEY UPDATE quantity = quantity $operator 1";
    $cart_query = $link->query($cart_items);
    $cart_item_id = $link->insert_id;
    $cart_info = mysqli_fetch_assoc($cart_query);
    $joined_cart = "SELECT cartItems.cartItemId
                        AS id, cartItems.productId, cartItems.quantity, products.name, products.price, products.image, products.shortDescription
                      FROM cartItems
                      JOIN products
                        ON cartItems.productId = products.productId
                     WHERE cartItems.cartItemId = $cart_item_id";
    $joined_query = $link->query($joined_cart);
    $joined_info = mysqli_fetch_assoc($joined_query);
    $response['body'] = $joined_info;
    $_SESSION['cart_id'] = $cart_id;
    send($response);
  } else {
    $cart_id = $_SESSION['cart_id'];
    $cart_items = "INSERT INTO cartItems (cartId, productId, price, quantity)
                        VALUES ($cart_id, $product_id, $product_price, 1)
                            ON DUPLICATE
                    KEY UPDATE quantity = quantity $operator 1";
    $cart_query = $link->query($cart_items);
    $cart_item_id = $link->insert_id;
    $cart_info = mysqli_fetch_assoc($cart_query);
    $joined_cart = "SELECT cartItems.cartItemId
                        AS id, cartItems.productId, cartItems.quantity, products.name, products.price, products.image, products.shortDescription
                      FROM cartItems
                      JOIN products
                        ON cartItems.productId = products.productId
                     WHERE cartItems.cartItemId = $cart_item_id";
    $joined_query = $link->query($joined_cart);
    $joined_info = mysqli_fetch_assoc($joined_query);
    $response['body'] = $joined_info;
  }
  send($response);
}

if ($request['method'] === 'DELETE') {
  $cart_item_id = $request['body']['cartItemId'];
  $cart_id = $_SESSION['cart_id'];
  $delete_query = "DELETE FROM cartItems
                         WHERE cartItems.cartItemId = $cart_item_id
                           AND cartItems.cartId = $cart_id";
  mysqli_query($link, $delete_query);
  $response['body'] = 'Item removed from cart';
  send($response);
}
