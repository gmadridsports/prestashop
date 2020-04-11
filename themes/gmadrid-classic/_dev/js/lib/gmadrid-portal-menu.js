$(window).on('scroll', function (e) {
  var scroll = $(window).scrollTop();

  if (scroll > 84)
    $('header.fusion-header-wrapper .fusion-header').addClass('fusion-sticky-shadow');
  else {
    $('header.fusion-header-wrapper .fusion-header').removeClass('fusion-sticky-shadow');
  }
});

$(document).ready(function() {
  var url = window.location.href;
  var lastPart = url.substr(url.lastIndexOf('/') + 1);
  var itemToHighlight =  null;

  if (
    lastPart.startsWith("iniciar-sesion?back=my-account") ||
    lastPart.startsWith("mi-cuenta") ||
    lastPart.startsWith("datos-personales") ||
    lastPart.startsWith("direcciones") ||
    lastPart.startsWith("historial-compra") ||
    lastPart.startsWith("facturas-abono") ||
    lastPart.startsWith("direccion")
  ) {
    itemToHighlight = "menu-item-9641"
  } else {
    itemToHighlight = "menu-item-9640"
  }


    $("#" + itemToHighlight).addClass("current_page_item")
});
