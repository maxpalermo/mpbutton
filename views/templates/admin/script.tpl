{**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*}
<script type="text/javascript">
    $(document).ready(function(){

    });
    function mpbutton_ToggleActive(button)
    {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            useDefaultXhrHeader: false,
            url: '{$ajax_url}',
            data: 
            {
                ajax: true,
                action: 'toggleStatus',
                token: '{$ajax_token}',
                id_mp_button: $(button).attr('value')
            }
        })
        .done(function(result){
            if (result.status === false) {
                $.growl.error({
                    'title' : '{l s='Error' mod='mpbutton'}',
                    'message' : result.message
                });
                return false;
            }
            if (result.toggle === 1) {
                $(button).closest('tr').find('td:nth-child(4)').find('i').removeClass('icon-times').addClass('icon-check').css('color', '#72C279');
            } else {
                $(button).closest('tr').find('td:nth-child(4)').find('i').removeClass('icon-check').addClass('icon-times').css('color', '#C27279');
            }
        })
        .fail(function(){
            jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
        });
    }
</script>