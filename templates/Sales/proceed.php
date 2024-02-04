<?php
/**
 * Created by FVdW.
 *
 * depot vente @ raspberry
 * User: francois
 * Date: 18/01/2021
 *
 * @copyright: 2021
 * @version $Revision: $
 *
 * @var \Cake\ORM\Entity $sale
 */

$this->prepend('script', $this->Html->script(['vuejs304', 'axios']));

// mise à plat des données items
$flat_items = [];
if (count($sale->items)) {
    foreach($sale->items as $item) {
        $flat_items[] = $item->flat('DB');
    }
}

$url_close_sale = '';
$url_ajax_search_item = Cake\Routing\Router::url(['controller' => 'Items', 'action' => 'ajaxSearch']);
$url_ajax_lock_item   = Cake\Routing\Router::url(['controller' => 'Items', 'action' => 'ajaxLock']);
$url_ajax_unlock_item = Cake\Routing\Router::url(['controller' => 'Items', 'action' => 'ajaxUnlock']);

define('DIV_H', '480px');

?>
<script type="application/ecmascript">
    const Items = {
        data() {
            return {
                sale_id:<?= $sale->id ?>,
                list: <?= json_encode($flat_items) ?>,
                ajax_running:false,
                mode:"idle", // idle|search|ajax_running|found|not-found|many-found
                search: {
                    id:null,
                    h:<?= $sale->is_happy_hour? '1':'0' ?>,
                    ref:'',
                    _csrfToken:"<?= $this->request->getAttribute('csrfToken') ?>",
                },
                found_items:[],
                msg: "",
            }
        },
        methods: {
            infoTTL() {
                if (this.list.length > 1) {
                    let ttl = this.list.reduce((acc, itm) => {
                        return acc + parseFloat(itm.price);
                    }, 0);
                    ttl = ttl.toFixed(2);
                    return `${this.list.length} articles ~ Total: ${ttl} €`;
                } else {
                    return `Un article ~ Total: ${this.list[0].price} €`;
                }
            },
            listItems(e) {
                e.preventDefault();
                // scroll down
                setTimeout(function () {
                    let dC = document.getElementById("idiv_scrollable");
                    dC.scrollTop = dC.scrollHeight
                }, 100);
            },
            addItem() {
                this.mode = "search";
                this.search.ref='';
                this.search.id=null; // recherche id | code barre
                setTimeout(function () {
                    let inp = document.getElementById("i_search");
                    inp.focus();
                }, 100);
            },
            lockItem(id, happy_hrs = 0) {
                if (this.mode=='ajax_running') return;
                this.mode = "ajax_running";
                let currentObj = this;
                let hhr = <?= $sale->is_happy_hour? 'happy_hrs? 1:0':'0' ?>;
                axios.get(`<?= $url_ajax_lock_item ?>/${id}?sale=${this.sale_id}&h=${hhr}`, {headers:{'content-type': 'application/json'}})
                    .then(function (response) {
                        if (response.data.status == "OK") {
                            // Ajout Item
                            currentObj.list.push(response.data.item);
                        } else {
                        }
                        if (response.data.msg) alert(response.data.msg);
                        currentObj.mode="idle";
                    })
                    .catch(function (error) {
                        currentObj.mode="idle";
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
            unlockItem(id) {
                if (this.mode=='ajax_running') return;
                if (!confirm("Supprimer cet article ?")) return;
                this.mode = "ajax_running";
                let currentObj = this;
                axios.get(`<?= $url_ajax_unlock_item ?>/${id}`, {headers:{'content-type': 'application/json'}})
                    .then(function (response) {
                        if (response.data.status == "OK") {
                            // Retier Item
                            currentObj.list = currentObj.list.filter(function(itm){if(itm.id != response.data.id)return itm;});
                        } else {
                        }
                        if (response.data.msg) alert(response.data.msg);
                        currentObj.mode="idle";
                    })
                    .catch(function (error) {
                        currentObj.mode="idle";
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
            ajaxSearch() {
                if (this.mode=='ajax_running') return;
                this.mode = "ajax_running";
                this.found_items = [];
                let currentObj = this;
                axios.post("<?= $url_ajax_search_item ?>", this.search, {headers:{'content-type': 'application/json'}})
                    .then(function (response) {
                        if (response.data.status == "OK") {
                            // trouvé
                            currentObj.found_items = response.data.items;
                            currentObj.msg = "";
                            if (currentObj.found_items.length > 1) {
                                currentObj.mode = "many-found";
                            } else {
                                currentObj.mode = "found";
                            }
                        } else {
                            currentObj.found={};
                            currentObj.msg=response.data.reason;
                            currentObj.mode="not-found";
                        }
                        if (response.data.msg) alert(response.data.msg);
                        currentObj.scrollDwn();
                    })
                    .catch(function (error) {
                        currentObj.mode="idle";
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
            closeSale() {
                if (!confirm("Finaliser cette vente ?")) return;
                this.ajax_running = true;
                document.location.href = "<?= $url_close_sale ?>";
            },
            scrollDwn() {
                window.scrollTo(0,document.body.scrollHeight);
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                // Will be executed when the DOM is ready
                console.log('$nextTick()...');
                this.scrollDwn();
            })
        },
    }
</script>
<h3>Caisse <?= $sale->cash_register->name ?>  ~  Vente n°<?= $sale->id ?><?= $sale->is_happy_hour? ' ~ <i class="fa fa-smile-o fa-spin" aria-hidden="true"></i>':'' ?></h3>
<div id="sale">
    <div id="items" style="height:<?= DIV_H ?>">
        <p class="lead">Liste des articles <span v-if="list.length" class="badge badge-dark" style="font-size:110%;float: right">{{infoTTL()}}</span></p>
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
                    <th class="text-right">Prix</th>
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
                    <td class="text-right">{{item.price.toFixed(2)}} &euro;</td>
                    <td class="text-center"><i v-if="item.happy_hour" class="fa fa-smile-o lead" aria-hidden="true" title="'Happy hour' appliqué"></i>&nbsp;</td>
                    <td>
                        <button class="btn btn-outline-dark" @click="unlockItem(item.id)"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 mb-2">
        <div v-show="mode=='search'" class="text-center">
            <label>Article : </label>
            <input v-model="search.ref" id="i_search" type="text" class="form-control" size="15" maxlength="50" name="search" style="display: inline;width: auto" placeholder="N° ou code barre" @keyup.enter="ajaxSearch" />
            <button class="btn btn-success" @click="ajaxSearch" title="Chercher l'article par son numéro ou code barre"><i class="fa fa-search fa-lg" aria-hidden="true"></i></button>
            <button class="btn btn-danger ml-4" @click="mode='idle'" title="Annuler la recherche" ><i class="fa fa-times fa-lg" aria-hidden="true"></i></button>
        </div>
        <div v-show="mode=='idle'" class="text-center">
            <button @click="addItem" class="btn btn-primary" >Ajouter un article</button>
            &nbsp;
            <?= $this->Html->link('Finaliser cette vente', ['action' => 'finalize', $sale->id], ['v-if' =>'list.length', 'class' => 'btn btn-primary', 'escape' => false]) ?>
            <?= $this->Html->link('Annuler cette vente', ['action' => 'cancel', $sale->id], ['v-if' =>'list.length==0', 'class' => 'btn btn-outline-danger', 'escape' => false]) ?>
        </div>
        <div v-show="mode=='ajax_running'" class="text-center">
            <?= $this->Html->image('wait.gif') ?>
        </div>
        <div v-show="mode=='found'|| mode=='many-found'" class="alert alert-success">
            <h4 v-show="mode=='found'">Article {{search.ref}} trouvé</h4>
            <h4 v-show="mode=='many-found'">>Plusieurs articles trouvés pour {{search.ref}}</h4>
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>N°</th>
                    <th>Description</th>
                    <th>&nbsp;</th>
                    <th>N° fabricant</th>
                    <th>Code barre</th>
                    <th class="text-right">Prix</th>
                    <th >&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item, id in found_items" :key="id">
                    <td class="lead">{{item.deposit_id}}-{{item.id}}</td>
                    <td>{{item.name}}</td>
                    <td class="text-center"><i v-if="item.color" class="fa fa-circle lead" aria-hidden="true" :style="{color:item.color}"></i>&nbsp;</td>
                    <td style="font-size: 90%">{{item.mfr_part_no}}</td>
                    <td style="font-size: 90%">{{item.bar_code}}</td>
                    <td class="text-right">{{item.display_price}}</td>
                    <td class="text-center">
                        <button @click="lockItem(item.id, 0)" class="btn btn-light"><i class="fa fa-check-circle" aria-hidden="true"></i> Ajouter</button>
                        <?php if ($sale->is_happy_hour) : ?>
                        &nbsp;
                        <button v-if="item.happy_hour" @click="lockItem(item.id, 1)" class="btn btn-light"><i class="fa fa-smile-o text-success" aria-hidden="true"></i> Ajouter</button>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="text-center"><button class="btn btn-dark" @click="mode='idle'"> Ignorer : nouvelle recherche </button></div>
        </div>
        <div v-show="mode=='not-found'" class="alert alert-danger">
            <h4>Article {{search.ref}} non trouvé</h4>
            <p>{{msg}}</p>
            <div class="text-center"><button class="btn btn-dark" @click="mode='idle'"> Fermer </button></div>
        </div>
    </span>
    </div>
</div>
<?php
$this->append('script', '<script>Vue.createApp(Items).mount("#sale")</script>');
