<!-- eslint-disable vue/no-use-v-if-with-v-for,vue/no-confusing-v-for-v-if -->

<template>
    <mu-dialog :title="modalTitle" :open.sync="orderDialogState" class="order-lav-dialog">
       
        <div class="loader" v-if="!pageLoaded">
            <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
        </div>
        
        <mu-paper :z-depth="1">
            
            <mu-data-table 
                :columns="table.columns"
                :data="table.data"
                :rowKey="'history-row-'+ randomId()"
                class="history-table"
                max-height="420"
                >
                
                <template slot="expand" slot-scope="scope">
                   
                    <!-- SUB TABLE: NEW ORDER -->
                    <div class="history-details-table-wrap" v-if="scope.row.action == 'new'">
                        <table v-if="scope.row.data" class="history-details-table details-table-new">
                            <thead></thead>
                            <tbody>
                                <tr v-for="(value, key, index) in scope.row.data" :key="'history-subrow-'+ index">
                                    <td>{{ table.fields[key].label }}</td>
                                    <td v-html="getFieldValue(key, value)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- SUB TABLE: EDIT ORDER -->
                    <div class="history-details-table-wrap" v-if="scope.row.action == 'edit'">
                        <table v-if="scope.row.data" class="history-details-table details-table-edit">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>PRIMA</th>
                                    <th>DOPO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(value, key, index) in scope.row.data.updated" :key="'history-subrow-'+ index" v-if="getFieldValue(key, value.old) != getFieldValue(key, value.new)">
                                    <td>{{ table.fields[key].label }}</td>
                                    <td v-html="getFieldValue(key, value.old)"></td>
                                    <td v-html="getFieldValue(key, value.new)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- SUB TABLE: EDIT ORDER STATUS 0 -->
                    <div class="history-details-table-wrap" v-if="scope.row.action == 'edit-status_0'">
                        <table v-if="scope.row.data" class="history-details-table details-table-edit_status_0">
                            <thead></thead>
                            <tbody>
                                <tr>
                                    <td>{{ table.fields['order-oven_id'].label }}</td>
                                    <td v-html="getFieldValue('order-oven_id', scope.row.data['order-oven_id'])"></td>
                                </tr>
                                <tr>
                                    <td>{{ getFieldKey('order-lav-user', scope.row) }}</td>
                                    <td v-html="getFieldValue('order-lav-user', scope.row.data['order-lav-user'])"></td>
                                </tr>
                            </tbody>
                        </table>

                        <table v-if="scope.row.data" class="history-details-table details-table-edit_status_0" style="margin: 20px 0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>PRIMA</th>
                                    <th>DOPO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(value, key, index) in scope.row.data.updated" :key="'history-subrow-'+ index" v-if="getFieldValue(key, value.old) != getFieldValue(key, value.new)">
                                    <td>{{ getFieldKey(key, scope.row) }}</td>
                                    <td v-html="getFieldValue(key, value.old)"></td>
                                    <td v-html="getFieldValue(key, value.new)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- SUB TABLE: EDIT ORDER STATUS 1 -->
                    <div class="history-details-table-wrap" v-if="scope.row.action == 'edit-status_1' || scope.row.action == 'edit-status_2'">
                        <table v-if="scope.row.data" class="history-details-table details-table-edit_status_0">
                            <thead></thead>
                            <tbody>
                                <tr>
                                    <td>{{ table.fields['order-oven_id'].label }}</td>
                                    <td v-html="getFieldValue('order-oven_id', scope.row.data['order-oven_id'])"></td>
                                </tr>
                                <tr>
                                    <td>Responsabile</td>
                                    <td v-html="getFieldValue('order-lav-user', scope.row.data['order-lav-user'])"></td>
                                </tr>
                            </tbody>
                        </table>

                        <table v-if="scope.row.data" class="history-details-table details-table-edit_status_0" style="margin: 20px 0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>PRIMA</th>
                                    <th>DOPO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(value, key, index) in scope.row.data.updated" :key="'history-subrow-'+ index" v-if="getFieldValue(key, value.old) != getFieldValue(key, value.new)">
                                    <td>{{ getFieldKey(key, scope.row) }}</td>
                                    <td v-html="getFieldValue(key, value.old)"></td>
                                    <td v-html="getFieldValue(key, value.new)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                </template>
                
                <template slot-scope="scope">
                    <td class="is-center">{{ scope.row.date | moment('DD/MM/YYYY HH:mm') }}</td>
                    <td class="is-center">{{ getActionUser(scope.row) }}</td>
                    <td v-html="getOrderHistory(scope.row)"></td>
                </template>
            </mu-data-table>
        
        </mu-paper>
        
        <div class="dialog-foot">
            <mu-button color="var(--color-orange)" @click="viewOrder(order.id)" v-if="this.$route.query.source != 'Ordine'">Visualizza Ordine</mu-button>
            <mu-button color="var(--color-orange)" @click="viewStatus(order.id)">Stato Lavorazione</mu-button>
            <mu-button slot="actions" flat color="primary" @click="closeOrderDialog()">
                {{ (this.$route.query.source == 'Ordine') ? 'Torna all\'ordine' :  'Chiudi' }}
            </mu-button>
        </div>
        
    </mu-dialog>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    
    @import '../assets/css/components/orderHistoryDialog.css';

</style>

<script>
    
    /* eslint-disable */
    
    const moment = require('moment');
    
    export default {
        name: 'orderHistoryDialog',
        props: {
            user: {
                username: 'guest',
                role: 'guest'
            }
        },
        data() {
            return {
                moment: moment,
                modalTitle: '',
                pageLoaded: false,
                orderDialogState: false,
                options: {},
                order: {},
                table: {
                    columns: [
                        { title: 'Data', name: 'date', width: 120, align: 'center', sortable: false },
                        { title: 'Utente', name: 'user', width: 90, align: 'center', sortable: false },
                        { title: 'Azione', name: 'data', align: 'left', sortable: false }
                    ],
                    data: [],
                    fields: []
                }
            }
        },
        watch: {
            $route: function() {
                if(this.$route.name == "Storia Ordine") {
                    
                    this.pageLoaded = false;
                    this.openOrderDialog();   
                } else {
                    this.orderDialogState = false;
                }
            },
            orderDialogState: function() {
                if(!this.orderDialogState && this.$route.name == 'Storia Ordine') this.$router.push({ name: 'Cruscotto Ordini' });
            }
        },
        created() {
            
            if(this.$route.name == "Storia Ordine" ) this.openOrderDialog();
            
        },
        methods: {
            openOrderDialog: function() {
                
                this.resetData();
                
                this.orderDialogState = true;
                
                this.getOptions();
                this.getOrderData();
            },
            closeOrderDialog: function() {
                
                this.orderDialogState = false;
                if(!this.$route.query.source) {
                    this.$router.push({ name: 'Cruscotto Ordini' });
                } else {
                    this.$router.push({ name: this.$route.query.source, params: { action: 'view', orderData: this.order.id } });
                }
            },
            viewOrder: function(order_id) {
                
                this.$router.push({ name: 'Ordine', params: { action: 'view', orderData: order_id }, query: { source: 'Stato Ordine' } });
            },
            viewStatus: function(order_id) {
                
                if(this.$route.query.source) {
                    this.$router.push({ name: 'Stato Ordine', params: { orderData: order_id }, query: { source: this.$route.query.source } });
                } else {
                    this.$router.push({ name: 'Stato Ordine', params: { orderData: order_id } });
                }
            },
            getOptions: function() {
                
                var $this = this;
                
                this.$http.get(this.$app.api.host +'/wp-json/acf/v3/options/ordini_options', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(function(response) {
                    
                    $this.options = response.body.acf;
                    
                });
            },
            getOrderData: function() {
                
                var $this = this;
                
                this.$http.get(this.$app.api.host +'/wp-json/wp/v2/ordini/'+this.$route.params.orderData, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                }).then(function(response) {
                    
                    response.body.slug = response.body.slug.replace('order-', '');
                    $this.order = response.body;
                    
                    $this.initOrder($this);
                });
            },
            initOrder: function($this) {

                $this.modalTitle = ($this.order.acf['order-index'] != 0 && $this.order.acf['order-type'] == 'order') ? 'Modifica Stato Ordine #'+ $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : 'Modifica Stato Ordine #'+ $this.order.acf['order-oven_id'];

                let p = $this.order.acf['order-history'].replace(/\\/g, "");
                $this.order.acf['order-history'] = JSON.parse(p);
                $this.table.data = $this.order.acf['order-history'];

                $this.$http.get(this.$app.api.host +'/wp-json/wp/v2/acf/get-fields', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    params: {
                       id: this.order.id
                    }
                }).then(function(response) {
                    
                    $this.table.fields = response.body;
                    $this.pageLoaded = true;
                    
                });
                
            },
            getOrderHistory: function(history) {
                
                var label = '';
                
                if(history.action == 'new') {
                    
                    label = '<p>Ordine creato</p>';
                    
                } else if(history.action.indexOf('toggle_flag-') > -1) {
                    
                    if(history.data && history.action.indexOf('order-alert') > -1) {
                        if(history.data['order-alert'] == 'true' || history.data['order-alert'] == true) {
                            label = '<p>Ordine contrassegnato come <b>LETTO</b></p>';
                        } else {
                            label = '<p>Ordine contrassegnato come <b>DA LEGGERE</b></p>';
                        }
                    } else if(history.data && history.action.indexOf('order-lav-edit_') > -1) {
                        var curr_field = history.action.replace('toggle_flag-', '');
                        var curr_field_value = (history.data[curr_field]) ? 'SI' : 'NO';
                        if(this.table.fields[curr_field]) {
                            label = '<p>'+ this.table.fields[curr_field].label +': <b>'+ curr_field_value +'</b></p>';
                        }
                    }
                    
                } else if(history.action == 'edit') {
                    
                    label = '<p>Ordine modificato!</p>';
                    
                } else if(history.action == 'edit-status_0') {
                    
                    label = '<p>Stato lavorazione ordine modificato <br> <span style="font-size: 10px">(⚠️ Materiale mancante segnalato!)</span></p>';
                    
                } else if(history.data && (history.action == 'edit-status_1' || history.action == 'edit-status_2')) {
                    
                    label = '<p>Stato lavorazione cambiato in: <b style="text-transform: uppercase">'+ history.data.updated['order-lav-status'].new.label +'</b><br></p>';   
                }
                
                return label
            },
            getFieldKey: function(key, data = null) {
                
                var new_key = '';
                
                if(key == 'order-lav-user') {
                    
                    var value = '';
                    
                    if(data.action == 'edit-status_0') {
                        
                        value = this.order.acf['order-lav-status'].value;
                        
                    } else if(data.action == 'edit-status_1' || data.action == 'edit-status_2') {
                        
                        value = data.data.updated['order-lav-status'].old.value;   
                    }
                    
                    switch(value) {
                        case 'lav-status-0':
                            new_key = this.table.fields[key][0];
                            break;
                        case 'lav-status-1':
                            new_key = this.table.fields[key][0];
                            break;
                        case 'lav-status-2':
                            new_key = this.table.fields[key][1];
                            break;
                        case 'lav-status-3':
                            new_key = this.table.fields[key][2];
                            break;
                        case 'lav-status-4':
                            new_key = this.table.fields[key][3];
                            break;
                        case 'lav-status-5':
                            new_key = this.table.fields[key][4];
                            break;
                    }
                    
                } else if(key == 'order-lav-parts') {

                    new_key = this.table.fields[key][0];
                            
                } else {
                    
                    new_key = this.table.fields[key];
                }
                
                return new_key.label
                
            },
            getFieldValue: function(key, value) {
                
                var new_value = value;
                
                if(key == 'order-gallery' || key == 'order-lav-gallery') {

                    var imgs = '';
                    if(value) {
                        value.forEach(function(el, index) {
                            imgs = imgs +'<img src="'+ el.path +'" style="height: 50px; margin-right: 5px;" alt="Immagine eliminata!"/>';
                        });
                        new_value = imgs;
                    } else {
                        new_value = '';
                    }

                } else {
                    
                    if(typeof value === 'object' || typeof value === 'array') {
                        if(Array.isArray(value)) {
                            new_value = '';
                            value.forEach(function(el, index) {
                                if(el.label && el.label != 'undefined') {
                                    new_value = new_value +''+ el.label +', ';
                                }
                            });
                        } else {
                            if(value.label) new_value = value.label;
                        }
                    } else if(key.indexOf('date') > -1) {
                        if(value) {
                            if(value.indexOf('T') >= 0) {
                                new_value = this.$moment(value).format('DD/MM/YYYY');
                            } else {
                                new_value = value.replace(/-/g, '/');
                            }
                            if(new_value === 'Invalid date') new_value = value;
                        } else {
                            new_value = '';
                        }
                    } else {
                        if(value == 'false' || value == 'true') {
                            new_value = (value == 'true') ? 'SI' : 'NO';
                        } else if(value == 'undefined') {
                            new_value = '';
                        } else {
                            new_value = new_value;
                        }
                    }
                }
                
                return new_value
            },
            getActionUser: function(history) {
                
                var user = '';
                
                if(history.data && history.data['order-user']) {
                    user = history.data['order-user'].label;
                } else if(history.data && history.data['order-lav-user']) {
                    for(var index in history.data['order-lav-user']) {
                        if(history.data['order-lav-user'][index].label && history.data['order-lav-user'][index].label != 'undefined') {
                            user = user +''+ history.data['order-lav-user'][index].label +', ';   
                        }
                    }
                } else {
                    user = history.user.username;
                }
                
                return user
            },
            sendMail: function($this, from, to, subject, message) {
                
                var options = {
                    emulateJSON: true
                },
                params = {
                    from: from,
                    to: to,
                    subject: subject,
                    message: message
                };
                
                $this.$http.post($this.$app.urls.dist +'/api/mailer.php', params, options).then(function(response) {
                    
                    setTimeout(function() {
                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Notifica inviata con successo!',
                            status: response.ok
                        });
                    }, 3000);
                    
                }, function(error) {
                    
                    setTimeout(function() {
                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Impossibile inviare la notifica! (#'+ error.status +')',
                            status: error.ok
                        });
                    }, 3000);
                });
            },
            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },
            inArray: function (array, value) {
                return array.indexOf(value) > -1 ? true : false;
            },
            randomId: function() {
                return Math.random().toString(36).substr(2, 9);
            },
            onImageLoadFailure: function(event) {
                event.target.src = '../assets/images/not-found.png'
            }
        },
        components: {
            
        }
    }

</script>