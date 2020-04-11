"use strict";

import $ from "jquery";
import "./cart.js";

class GmadridSubscription {
  _membershipProductId = null;
  _authenticationToken = null;
  _checkoutUrl = null;

  constructor(membershipProductId, authenticationToken, checkoutUrl) {
    this._membershipProductId = membershipProductId;
    this._authenticationToken = authenticationToken;
    this._checkoutUrl = checkoutUrl
  }

  async deleteProductFromCart(productId, idProductAttribute) {
    return new Promise((resolutionFunc,rejectionFunc) => {
      const requestData = {
        ajax: '1',
        action: 'update'
      };
      const cartAction = {
        url: `/index.php?controller=cart&delete=1&id_product=${productId}&id_product_attribute=${idProductAttribute}&token=${this._authenticationToken}`,
        type: 'delete-from-cart'
      };

      $.ajax({
        url: cartAction.url,
        method: 'POST',
        data: requestData,
        dataType: 'json',
        beforeSend: function (jqXHR) {}
      }).success(resolutionFunc)
        .error(rejectionFunc)
    });
  }

  async emptyCart() {
    // empty the cart
    for (const product of prestashop.cart.products) {
      const productId = product.id_product;
      const idProductAttribute = product.id_product_attribute;
      await this.deleteProductFromCart(productId, idProductAttribute, this._authenticationToken);
    }

    return true;
  }

  async addMembershipProduct() {
    return new Promise((resolutionFunc,rejectionFunc) => {
      const cartAction = {
        url: `/index.php?controller=cart&add=1&id_product=${this._membershipProductId}&token=${this._authenticationToken}`,
        type: 'update-cart'
      };

      const requestData = {
        ajax: '1',
        action: 'update'
      };
      $.ajax({
        url: cartAction.url,
        method: 'POST',
        data: requestData,
        dataType: 'json',
        beforeSend: function (jqXHR) {
          // promises.push(jqXHR);
        }
      }).then(function (resp) {
        resolutionFunc(resp);
      }).fail(function (resp) {
        rejectionFunc(resp);
      });
    });
  }

  async processMembership() {
    await this.emptyCart();
    await this.addMembershipProduct();
  }
}

class GMadridSubscriptionButton {
  static _pristine_content = '<i class="material-icons" style="width: 5px !important">card_membership</i> Suscríbete';
  static _waiting_content = 'Preparando la compra...';
  static _redirecting_content = 'Pasando por la caja...';

  _element = null;
  _text = null;

  constructor(buttonId) {
    this._element = $(buttonId);
  }

  waiting() {
    this._element.html(GMadridSubscriptionButton._waiting_content);
    this._element.attr('disabled', true);
  }

  pristine() {
    this._element.html(GMadridSubscriptionButton._pristine_content);
    this._element.attr('disabled', false);
  }

  redirecting() {
    this._element.html(GMadridSubscriptionButton._redirecting_content);
    this._element.attr('disabled', true);
  }

}


$('#subscribe-action').on('click', (event) => {
  event.preventDefault();

  const button = new GMadridSubscriptionButton("#subscribe-action");
  const productId = $("#gmadrid-membership").data("membership-product-id");
  const checkoutUrl = $("#gmadrid-membership").data("checkout-url");
  const token = window.prestashop.static_token;
  button.waiting();
  const gmadridSubscription = new GmadridSubscription(productId, token, checkoutUrl);

  gmadridSubscription.processMembership().then(value => {
    button.redirecting();
    window.location = gmadridSubscription._checkoutUrl;
  }).catch(value => {
    alert('Ha ocurrido un error mientras preparábamos la compra. Por favor, reinténtalo o contacta con tu responsable de sección');
    button.pristine();
  });
});
