{if isset($gmadridMembership)}
  {if $gmadridMembership.is_member}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="gmadrid-membership"
       href="#"
       title="{l s='My membership details' mod='gmadridMembership'}">
  <span class="link-item">
  <i class="material-icons">  <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/logo-blanco-h.png" style="vertical-align: top; height: 40px; width: 40px;"></i>
    {l s='Número de socio' mod='gmadridMembership'}: {$gmadridMembership.membership_number}
  </span>
    </a>
  {elseif $gmadridMembership.is_past_membership}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="gmadrid-membership"
       href="#"
       title="{l s='My membership details' mod='gmadridMembership'}" data-checkout-url="{$gmadridMembership.checkout_url}" data-membership-product-id="{$gmadridMembership.membership_product_id}">
      <span class="link-item">
      <i class="material-icons">insert_emoticon</i>
      {l s='Ex socio' mod='gmadridMembership'} <br>
      <br>
      <button id="subscribe-action" class="btn btn-primary" name="subscribe" type="button"><i class="material-icons" style="width: 5px !important">card_membership</i>{l s='Renueva' mod='gmadridMembership'}</button>
      </span>
    </a>
  {else}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="gmadrid-membership"
       href="#"
       title="{l s='My membership details' mod='gmadridMembership'}" data-checkout-url="{$gmadridMembership.checkout_url}" data-membership-product-id="{$gmadridMembership.membership_product_id}">
      <span class="link-item">
      <i class="material-icons">insert_emoticon</i>
      {l s='You are a supporter' mod='gmadridMembership'} <br>
      <br>
      <button id="subscribe-action" class="btn btn-primary" name="subscribe" type="button"><i class="material-icons" style="width: 5px !important">card_membership</i>{l s='Suscríbete' mod='gmadridMembership'}</button>
      </span>
    </a>
  {/if}
{/if}
