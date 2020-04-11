{extends file='page.tpl'}

{block name='page_header_container'}{/block}

{block name='page_header_container'}{/block}

{block name='left_column'}
{/block}

{block name='page_content'}
  <button class="ladda-button" data-spinner-color="black" data-style="contract" style="background-color: white; border: none;height: 1.5rem;"></button>
  Preparando tu cuota anual, espera por favor...

    <script defer>
      window.onload= function() {
        var a = Ladda.create( document.querySelector( '.ladda-button' ) );
        a.start();

        $('document').ready(function(){
          prestashop.emit(
                  'subscribeGmadridAnnual',
                  {
                    membership_product_id: {$gmadridMembership.membership_product_id},
                    token: "{$gmadridMembership.checkout_url}",
                    checkout_url: "{$gmadridMembership.checkout_url}",
                  }
          );
        });
      };
    </script>
{/block}

