import React from 'react';

function Header(props) {
  const name = props.name;
  const itemCount = props.cartItems.length;
  return (
    <div className="bg-dark mb-5 p-3">
      <div className="container">
        <h5 className="text-light d-inline-block m-0"><i className="fas fa-dollar-sign"></i> {name}</h5>
        <p className="text-light d-inline-block float-right m-0">{itemCount} Items <i className="fas fa-shopping-cart"></i></p>
      </div>
    </div>
  );
}

export default Header;
