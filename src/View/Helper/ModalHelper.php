<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 04/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 *
 * Helper création Modal vanilla JS
 * source : https://github.com/harvesthq/chosen
 *
 * Fichiers webroot requis :
 *		webroot/js/modal.js
 * 		webroot/css/modal.css
 *
 */


namespace App\View\Helper;

use Cake\View\Helper;

class ModalHelper extends Helper
{
    const AJAX_DOM_ID = 'modal_ajax';
    public $helpers = ['Html'];
    private $dom_cntr = 1;

    private $div_containers = [];
    private $initJsCss_done = false;

    public function initJsCss()
    {
        if ($this->initJsCss_done) return;
        if (!count($this->div_containers)) return;
        $this->initJsCss_done = true;
        $this->Html->script('modal.js', ['block' => true]);
        $this->Html->css('modal.css', ['block' => true]);

        $this->Html->scriptStart(['block' => true]);
?>
var ajax_modal_domId = "<?= self::AJAX_DOM_ID ?>"; // used in modal.js
var modals = document.querySelectorAll("[data-modal]");
modals.forEach(function(trigger) { activateModal(trigger); });
<?php
        $this->Html->scriptEnd();


    }

    /**
     * staticModal()
     *
     * @param string $link_label
     * @param string $modal_content HTML content to be fit in the <div class="modal-content"> ... </div>
     * @param array $link_options cf HTML->link() options + param "div_dom_id" utlise pour <div id=div_dom_id>...
     */
    public function staticModal($link_label, $modal_content, $link_options = [])
    {
        if (empty($link_options['div_dom_id'])) {
            $dom_id = "modal{$this->dom_cntr}";
        } else {
            $dom_id = $link_options['div_dom_id'];
            unset($link_options['div_dom_id']);
        }
        $this->dom_cntr++;

        $s_opts = '';
        foreach ($link_options as $k => $val) $s_opts .= " $k=\"$val\"";
        $s = "<a href=\"javascript:void(0)\" data-modal=\"$dom_id\" $s_opts>$link_label</a>";
        $this->div_containers[ $dom_id ] = <<< MODAL
<div class="modal" id="{$dom_id}">
  <div class="modal-bg modal-exit"></div>
  <div class="modal-container">
    {$modal_content}
    <button class="modal-close modal-exit">&#10060;</button>
  </div>
</div>
MODAL;
        return $s;
    }

    /**
     * ajaxModal()
     *
     * @param string $link_label
     * @param string|array $ajax_url url for ajax call : the return data will be fit in the <div id="modal_container_ajax"> ... </div>
     * @param array $link_options cf HTML->link() options
     */
    public function ajaxModal($link_label, $ajax_url, $link_options = [])
    {
        if (is_array($ajax_url)) $ajax_url = \Cake\Routing\Router::url($ajax_url);
        $dom_id = self::AJAX_DOM_ID;
        $s_opts = '';
        foreach ($link_options as $k => $val) $s_opts .= " $k=\"$val\"";
        $s = "<a href=\"javascript:void(0)\" data-url=\"$ajax_url\" data-modal=\"$dom_id\" $s_opts>$link_label</a>";
        if (empty($this->div_containers[ $dom_id ])) {
            $this->div_containers[ $dom_id ] = <<< MODAL_AJAX
<div class="modal" id="{$dom_id}">
  <div class="modal-bg modal-exit"></div>
  <div class="modal-container">
    <div id="modal_container_ajax">
        &nbsp;
    </div>
    <button class="modal-close modal-exit">&#10060;</button>
  </div>
</div>
MODAL_AJAX;

        }
        return $s;

    }


    // ================================================================================
    // Callback
    /**
     * afterRender()
     *
     * @param \App\View\Helper\Event $event
     * @param $viewFile
     */
    public function afterRender(\Cake\Event\Event $event, $viewFile)
    {
        $layout = $this->getView()->getLayout();
        if ($layout == 'ajax') return;
        $this->initJsCss();
        if (count($this->div_containers)) {
            $this->_View->append('content');
            echo "\n<!-- Modal div's -->\n";
            echo join("\n\n", $this->div_containers);
            echo "\n<!-- end of Modal div's -->\n";
            $this->_View->end();
        }
    }
}
