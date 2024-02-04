<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 26/12/2020
 *
 * @copyright: 2020
 * @version $Revision: $
 *
 * @var \Cake\ORM\ResultSet $items
 * @var \App\Model\Entity\Deposit $deposit
 */


// mise à plat des données items
$flat_items = [];
if (count($items)) {
    foreach($items as $item) {
        $flat_items[] = $item->flat('REQ');
    }
}

define('NB_COLS', 6);  // tableau des couleurs : nb colonnes
define('DIV_H', '560px');

$colors = array_merge([''], array_keys(\Cake\Core\Configure::read('ui.colors')));

$url_ajax_save_item = Cake\Routing\Router::url(['controller' => 'Items', 'action' => 'ajaxSave']);
$url_ajax_delete_item = Cake\Routing\Router::url(['controller' => 'Items', 'action' => 'ajaxDelete']);
$url_close_deposit = Cake\Routing\Router::url(['controller' => 'Deposits', 'action' => 'close', $deposit->id]);

$this->prepend('script', $this->Html->script(['vuejs304', 'axios']));


?>
<script type="application/ecmascript">
    console.log("Register depot-vente");

    const Deposits = {
        data() {
            return {
                display: "list",
                list: <?= json_encode($flat_items) ?>,
                current: {
                    values: {
                        id:null,
                        name: "",
                        color: "",
                        bar_code: "",
                        mfr_part_no: "",
                        requested_price: 0,
                        happy_hour: 0,
                    },
                    errors: {
                        name: "",
                        color: "",
                        bar_code: "",
                        mfr_part_no: "",
                        requested_price: "",
                        happy_hour: "",
                    }
                },
                ajax_running:false,
            }
        },
        methods: {
            addItem() {
                this.resetCurrent();
                this.display = "frm_item";
                setTimeout(function(){ document.getElementById("name").focus()}, 100);
            },
            editItem(item_id) {
                if (this.ajax_running) return;
                this.setCurrent(item_id);
                this.display = "frm_item";
            },
            listItems(e) {
                e.preventDefault();
                if (this.display != "display") {
                    this.display = "list";
                    // scroll down
                    setTimeout(function(){ let dC = document.getElementById("idiv_scrollable");dC.scrollTop = dC.scrollHeight}, 100);
                }
            },
            closeDeposit() {
                if (!confirm("Clôturer ce dépôt ?")) return;
                this.ajax_running = true;
                document.location.href = "<?= $url_close_deposit ?>";
            },
            setCurrent(item_id) {
                for (let i=0; i < this.list.length; i++) {
                    if (this.list[i].id == item_id) {
                        this.current.values.id = this.list[i].id;
                        this.current.values.deposit_id = <?= $deposit->id ?>;
                        this.current.values.name = this.list[i].name;
                        this.current.values.color = this.list[i].color;
                        this.current.values.bar_code = this.list[i].bar_code;
                        this.current.values.mfr_part_no = this.list[i].mfr_part_no;
                        this.current.values.requested_price = this.list[i].requested_price;
                        this.current.values.happy_hour = this.list[i].happy_hour;
                        break;
                    }
                }
                this.resetError();
            },
            resetCurrent() {
                this.current.values.id = null;
                this.current.values.deposit_id = <?= $deposit->id ?>;
                this.current.values.name = "";
                this.current.values.color = "";
                this.current.values.bar_code = "";
                this.current.values.mfr_part_no = "";
                this.current.values.requested_price = "";
                this.current.values.happy_hour = 0;
                this.resetError();
            },
            resetError() {
                this.current.errors.name = "";
                this.current.errors.bar_code = "";
                this.current.errors.mfr_part_no = "";
                this.current.errors.requested_price = "";
            },
            updList(item) {
                let found = false;
                let new_item = {};
                new_item.id = item.id;
                new_item.deposit_id = <?= $deposit->id ?>;
                new_item.name = item.name;
                new_item.color = item.color;
                new_item.bar_code = item.bar_code;
                new_item.mfr_part_no = item.mfr_part_no;
                new_item.requested_price = item.requested_price;
                new_item.happy_hour = item.happy_hour;
                for (let i=0; i < this.list.length; i++) {
                    if (this.list[i].id == item.id) {
                        this.list[i] = new_item;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    // Ajout Item
                    this.list.push(new_item);
                }
            },
            colorClicked(color) {
                this.current.values.color = color;
            },
            formSubmit(e) {
                e.preventDefault();
                if (this.ajax_running) return;
                this.ajax_running = true;
                this.current.values._csrfToken = "<?= $this->request->getAttribute('csrfToken') ?>";
                this.current.deposit_id = <?= $deposit->id ?>;
                let currentObj = this;
                axios.post("<?= $url_ajax_save_item ?>", this.current.values, {headers:{'content-type': 'application/json'}})
                    .then(function (response) {
                        currentObj.ajax_running = false;
                        if (response.data.status == "ERROR") {
                            for (const [fld_name, err_message] of Object.entries(response.data.errors)) {
                                currentObj.current.errors[fld_name] = err_message;
                            }
                        } else if (response.data.status == "OK") {
                            currentObj.updList(response.data.item);
                            currentObj.setCurrent(response.data.item.id);
                            currentObj.display="show_item";
                        }
                        if (response.data.msg) alert(response.data.msg);
                    })
                    .catch(function (error) {
                        currentObj.ajax_running = false;
                        if (typeof error.response != "undefined") {
                            if (error.response.status == 403) {
                                alert("Votre session a expiré, vous devez vous re-connecter !");
                                window.location.reload(false);
                            } else {
                                console.log("catch...", error.response);
                            }
                        } else {
                            console.log("catch...", error);
                        }
                    });
            },
            removeItem(item_id) {
                if (this.ajax_running) return;
                this.setCurrent(item_id);
                if (!(confirm(`Supprimer l'article ${this.current.values.deposit_id}-${this.current.values.id} : ${this.current.values.name} ?` ))) return;
                this.ajax_running = true;
                let currentObj = this;
                axios.get(`<?= $url_ajax_delete_item ?>/${this.current.values.id}`)
                    .then(function (response) {
                        currentObj.ajax_running = false;
                        if (response.data.status == "OK") {
                            // suppression
                            currentObj.resetCurrent();
                            currentObj.list = currentObj.list.filter(itm => itm.id != response.data.item_id);
                        }
                        if (response.data.msg) alert(response.data.msg);
                    })
                    .catch(function (error) {
                        currentObj.ajax_running = false;
                        if (typeof error.response != "undefined") {
                            if (error.response.status == 403) {
                                alert("Votre session a expiré, vous devez vous re-connecter !");
                                window.location.reload(false);
                            } else {
                                console.log("catch...", error.response);
                            }
                        } else {
                            console.log("catch...", error);
                        }
                    });
            },
        },
    }
</script>
<style>
    div#idiv_show {
        margin:5%;
        border:2px solid #18bc9c;
        border-radius: 4px;
        padding:10px;
        font-size:120%
    }
    div.input.radio .form-check-inline label {
        margin: auto 16px auto auto;
    }
    table#itbl_colors {
        width: auto;
    }
    table#itbl_colors td {
        width:40px;
        text-align:center;
        font-size:1.5em;
    }
</style>
<h3>Enregistrement du dépôt n°<?= $deposit->id ?></h3>
<table class="table table-sm">
    <tr>
        <th style="max-width: 15%;text-align: right">Déposant</th>
        <td id="i_title_<?= $deposit->id ?>"><?= $deposit->title ?></td>
        <th style="text-align: right">Téléphone</th>
        <td id="i_tel_<?= $deposit->id ?>"><?= $deposit->numTel ?>&nbsp;</td>
        <th style="text-align: right">E-mail</th>
        <td id="i_email_<?= $deposit->id ?>"><?= $deposit->email ?>&nbsp;</td>
        <td>
            <?= $this->Modal->ajaxModal('<i class="fa fa-pencil-square-o" aria-hidden="true"></i>', ['controller'=>'Deposits', 'action' => 'ajaxEdit', $deposit->id],['class' => 'btn btn-outline-dark btn-sm', 'title' => 'Modifier les informations du déposant', 'escape' => false]) ?>
        </td>
    </tr>
</table>
<div id="deposit">
    <div id="items" v-show="display=='list'" style="height:<?= DIV_H ?>">
        <h3>Liste des articles</h3>
        <div v-if="list.length == 0" class="alert alert-info">
            Aucun article enregistré.
        </div>
        <div v-else style="max-height: 500px;overflow-y: scroll; border-bottom: solid 1px #2c3e50" class="pt-1 pb-1" id="idiv_scrollable">
            <table class="table table-striped table-sm" id="itbl_items">
                <thead>
                <tr>
                    <th>N°</th>
                    <th>Désignation</th>
                    <th>&nbsp;</th>
                    <th>N° fabricant</th>
                    <th>Code Barre</th>
                    <th style="width: 90px">Prix vente</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item, id in list" :key="id">
                    <td>{{item.deposit_id}}-{{item.id}}</td>
                    <td>{{item.name}}</td>
                    <td class="text-center"><i v-if="item.color" class="fa fa-circle lead" aria-hidden="true" :style="{color:item.color}"></i>&nbsp;</td>
                    <td style="font-size: 90%">{{item.mfr_part_no}}</td>
                    <td style="font-size: 90%">{{item.bar_code}}</td>
                    <td class="text-right">{{item.requested_price}} &euro;</td>
                    <td class="text-center"><i v-if="item.happy_hour" class="fa fa-smile-o lead" aria-hidden="true" title="'Happy hour' demandé"></i>&nbsp;</td>
                    <td>
                        <button class="btn btn-outline-dark btn-sm" @click="editItem(item.id)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                        &nbsp;
                        <button class="btn btn-outline-dark btn-sm" @click="removeItem(item.id)"><i class="fa fa-trash" aria-hidden="true"></i></i></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <span v-if="!ajax_running" >
                <?= $this->Html->link('Retour à la liste des dépôts', ['controller' => 'Deposits', 'action' => 'index'], ['class' => 'btn btn-outline-primary']) ?>
                &nbsp;
                <button class="btn btn-primary" @click="addItem">Ajouter un article</button>
                &nbsp;
                <button v-if="list.length" class="btn btn-primary" @click="closeDeposit">Clôtuer ce dépôt</button>
            </span>
            <span v-else>
                <?= $this->Html->image('wait.gif') ?>
            </span>
        </div>
    </div>
    <div v-show="display=='show_item'" style="height:<?= DIV_H ?>">
        <div id="idiv_show" class="row">
            <div class="col-md-4 lead" style="font-weight: 800;">
                {{ current.values.deposit_id}}-{{ current.values.id}}
            </div>
            <div class="col-md-4 lead"  style="font-weight: 800;">
                {{ current.values.requested_price }} &euro;
            </div>
            <div class="col-md-4 lead" style="font-weight: 800;">
                <i v-if="current.values.happy_hour" class="fa fa-smile-o lead" aria-hidden="true" title="'Happy hour' demandé"></i>
                &nbsp;
                <i v-if="current.values.color" class="fa fa-circle lead" aria-hidden="true" :style="{color:current.values.color}"></i>
            </div>
            <div class="col-md-12 pt-3 pb-5"  style="border-top: solid 1px #888">
                {{ current.values.name }}
            </div>
            <div class="col-md-6">
                Réf fabricant : {{ current.values.mfr_part_no }}
            </div>
            <div class="col-md-6">
                Code barre : {{ current.values.bar_code }}
            </div>
            <div class="col-md-12 text-center pt-3 pb-3">
                <button class="btn btn-success btn-lg" @click="listItems">  OK  </button>
            </div>
        </div>
    </div>
    <div v-show="display=='frm_item'" id="idiv_frm" style="height:<?= DIV_H ?>">
        <h3 v-if="!current.values.id">Ajouter un article</h3>
        <h3 v-else>Modifier l'article N° {{ current.values.deposit_id }}-{{ current.values.id }}</h3>
        <form id="ifrm_item" @submit="formSubmit">
            <div class="row">
                <div class="col-md-9">
                    <div class="input required">
                        <label for="name">Désignation</label>
                        <input v-model="current.values.name" class="form-control" id="name" name="name" required maxlength="200" />
                        <div v-if="current.errors.name" class="error-message">{{current.errors.name}}</div>
                        <span style="font-size: 90%">Seuls les caractères alpha-numériques et <code>. / - </code> sont acceptés.</span>
                    </div>
                    <div class="input">
                        <label for="mfr_part_no">N° fabricant / Réf. produit</label>
                        <div v-if="current.errors.mfr_part_no" class="error-message">{{current.errors.mfr_part_no}}</div>
                        <input v-model="current.values.mfr_part_no" class="form-control" id="mfr_part_no" name="mfr_part_no" maxlength="50" />
                    </div>
                </div>
                <div class="input col-md-3">
                    <label for="name">Couleur</label>
                    <table class="table table-sm" id="itbl_colors">
                        <?php
                        $l = count($colors);
                        for($i = 0; $i < $l; $i++) {
                            if ($i && !($i % NB_COLS)) echo '</tr>';
                            if (!($i % NB_COLS)) echo '<tr>';
                            $c = &$colors[$i];
                            echo '<td style="'.($c? 'background-color:'.$c : '').'" @click="colorClicked(\''.$c.'\')" />'.
                                '<input v-model="current.values.color" type="radio" name="color" value="'.$c.'" >'.
                                '</td>';
                        }
                        if ($i % NB_COLS) {
                            while ($i++ % NB_COLS) echo '<td>&nbsp;</td>';
                            echo '</tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="input col-md-5">
                    <label for="bar_code">Code barre</label>
                    <input v-model="current.values.bar_code" class="form-control" id="bar_code" name="bar_code" maxlength="50">
                    <div v-if="current.errors.bar_code" class="error-message">{{current.errors.bar_code}}</div>
                </div>
                <div class="input required col-md-4">
                    <label for="requested_price">Prix de vente</label>
                    <input v-model="current.values.requested_price" class="form-control" id="requested_price" name="requested_price" required="required" type="number" min="0" max="5000" step="0.1" style="max-width: 120px;">
                    <div v-if="current.errors.requested_price" class="error-message">{{current.errors.requested_price}}</div>
                </div>
                <div class="input radio col-md-3">
                    <label for="happy_hour" style="display: block">Happy Hour ?</label>

                    <div class="form-check form-check-inline">
                        <input v-model="current.values.happy_hour" class="form-check-input" type="radio" name="happy_hour" id="happy_hour_1" value="1" checked>
                        <label class="form-check-label" for="happy_hour_1">Oui</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input v-model="current.values.happy_hour" class="form-check-input" type="radio" name="happy_hour" id="happy_hour_0" value="0">
                        <label class="form-check-label" for="happy_hour_0">Non</label>
                    </div>
                </div>
                <div class="input radio col-md-6">
                    &nbsp;
                </div>
            </div>
            <div class="text-center">
                <span v-if="!ajax_running">
                    <button class="btn btn-outline-primary" @click="listItems">Retour</button>
                    &nbsp;
                    <input type="submit" class="btn btn-primary" :value="current.values.id? 'Mettre à jour' : 'Ajouter cet article'">
                </span>
                <span v-else>
                    <?= $this->Html->image('wait.gif') ?>
                </span>
            </div>
        </form>
    </div>
</div>
<?php
$this->append('script', '<script>Vue.createApp(Deposits).mount("#deposit")</script>');

// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
$this->Html->scriptStart(['block' => true]);  ?>
var elt = function(dom_id) { return document.getElementById(dom_id);}

_ajaxError = function(error) {
    ajax_running = false;
    if (typeof error.response != "undefined") {
        if (error.response.status == 403) {
            alert("Votre session a expiré, vous devez vous re-connecter !");
            window.location.reload(false);
        } else {
            alert("Erreur : "+error.response.status)
            console.log("catch...", error.message);
        }
    } else {
        alert("Erreur indéterminée !")
        console.log("catch...", error);
    }
}

var ajax_running = false;

saveDeposit = function (frm, add) {
    if (ajax_running) return;
    ajax_running = true;
    let l = document.querySelectorAll("#modal_container_ajax .sbmt");
    if (l.length)
        l[0].innerHTML = '<i class="fa fa-spinner fa-spin fa-2x"></i>';
    let frm_data = new FormData(frm);
    axios.post(frm.action, frm_data)
        .then(function (response) {
            ajax_running = false;
            let div = elt("modal_container_ajax");
            if (div) {
                div.innerHTML = response.data;
                let s = elt("ijs_return");
                if (s) eval(s.innerHTML);
        }
    })
    .catch(function (error) { _ajaxError(error); });
}
<?php $this->Html->scriptEnd();
// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
