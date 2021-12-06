<!-- eslint-disable vue/no-use-v-if-with-v-for,vue/no-confusing-v-for-v-if -->

<template>
    <mu-dialog :title="modalTitle" :open.sync="orderDialogState" class="order-lav-dialog">
       
        <div class="loader" v-if="!pageLoaded">
            <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
        </div>
        
        <div v-if="pageLoaded && user.role == 'admin'" class="order-tools">
           
            <mu-tooltip content="Modifia data di consegna">
                <mu-button class="btn-alert" icon :color="(formData['order-lav-edit_date']) ? 'var(--color-orange)' : 'var(--color-gray2)'" @click="toggleFlag('order-lav-edit_date')">
                    <img src="../assets/images/icons/edit-date.png">
                    <mu-icon :value="(formData['order-lav-edit_date']) ? 'check_circle' : 'panorama_fish_eye'"></mu-icon>
                </mu-button>    
            </mu-tooltip>
            
            <mu-tooltip content="Modifica dati tecnici">
                <mu-button class="btn-alert" icon :color="(formData['order-lav-edit_oven_props']) ? 'var(--color-orange)' : 'var(--color-gray2)'" @click="toggleFlag('order-lav-edit_oven_props')">
                    <img src="../assets/images/icons/edit-oven_props.png">
                    <mu-icon :value="(formData['order-lav-edit_oven_props']) ? 'check_circle' : 'panorama_fish_eye'"></mu-icon>
                </mu-button>
            </mu-tooltip>
            
            <mu-tooltip content="Modifica note">
                <mu-button class="btn-alert" icon :color="(formData['order-lav-edit_note']) ? 'var(--color-orange)' : 'var(--color-gray2)'" @click="toggleFlag('order-lav-edit_note')">
                    <img src="../assets/images/icons/edit-note.png">
                    <mu-icon :value="(formData['order-lav-edit_note']) ? 'check_circle' : 'panorama_fish_eye'"></mu-icon>
                </mu-button>
            </mu-tooltip>
            
            <mu-tooltip content="Modifiche trasporto">
                <mu-button class="btn-alert" icon :color="(formData['order-lav-edit_transport']) ? 'var(--color-orange)' : 'var(--color-gray2)'" @click="toggleFlag('order-lav-edit_transport')">
                    <img src="../assets/images/icons/edit-transport.png">
                    <mu-icon :value="(formData['order-lav-edit_transport']) ? 'check_circle' : 'panorama_fish_eye'"></mu-icon>
                </mu-button>
            </mu-tooltip>
            
        </div>
        
        <mu-form id="order-form" ref="form" :model="formData" class="order-form" v-if="pageLoaded">
        
            <div class="form-head"></div>
           
            <div class="form-body">
                
                <p>{{ order.acf['order-date'] | moment('DD/MM/YYYY') }} - {{ order.acf['order-oven_model']['label'] }} - {{ order.acf['order-oven_diameter']['label'] }} - {{ order.acf['order-oven_topcoat']['label'] }} - {{ order.acf['order-oven_mouth']['label'] }} - {{ order.acf['order-oven_fuel']['label'] }} - {{ order.acf['order-oven_fuel_side']['label'] }} - {{ (order.acf['order-oven_etl']) ? 'SI' : 'NO' }}
                </p>
                
                <mu-stepper :active-step="orderStep" orientation="vertical">
                    <mu-step v-for="(value, key, index) in formSelectOptions['order-lav-status']" :key="index">
                        
                        <mu-step-label>
                            <mu-icon slot="icon" value="warning" color="var(--color-yellow)" v-if="orderStep == index && formData['order-lav-parts'].length"></mu-icon>
                            <mu-icon slot="icon" value="check_circle" color="var(--color-green)" v-if="orderStep > index"></mu-icon>
                            {{ value }} {{ (formSelectOptions['order-lav-status']['lav-status-'+ (index + 1)]) ? ' > '+ formSelectOptions['order-lav-status']['lav-status-'+ (index + 1)] +' ?' : '' }}
                        </mu-step-label>
                        
                        <mu-step-content>
                            <table class="form-table" v-if="orderStep < 5">
                                <tr class="row-user">
                                    <td colspan="4">
                                        <div class="td-wrap">
                                            <b>Responsabile <span>(*)</span></b>
                                            <mu-form-item prop="order-lav-user" :rules="formRules['order-lav-user']">
                                                <mu-checkbox 
                                                    v-for="(value, key, index) in formSelectOptions['order-lav-user'][orderStep]" 
                                                    :key="index"
                                                    v-model="formData['order-lav-user']"
                                                    :value="key" 
                                                    :label="value"
                                                    color="var(--color-orange)"></mu-checkbox>
                                            </mu-form-item>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="row-parts" v-if="formSelectOptions['order-lav-parts'][orderStep]">
                                    <td colspan="4">
                                        <div class="td-wrap">
                                            <b>Parti mancanti</b>
                                            <mu-form-item prop="order-lav-parts" :rules="formRules['order-lav-parts']">
                                                <mu-checkbox 
                                                    v-for="(value, key, index) in formSelectOptions['order-lav-parts'][orderStep]" 
                                                    :key="index"
                                                    v-if="formSelectOptions['order-lav-parts'][orderStep]"
                                                    v-model="formData['order-lav-parts']"
                                                    :value="key" 
                                                    :label="value"
                                                    :class="key"
                                                    color="var(--color-orange)"></mu-checkbox>
                                                <mu-form-item prop="order-lav-parts_custom" :rules="formRules['order-lav-parts_custom']" v-if="inArray(formData['order-lav-parts'], 'lav-parts-0')">
                                                    <mu-text-field 
                                                        prop="order-lav-parts_custom" 
                                                        v-model="formData['order-lav-parts_custom']"
                                                        color="var(--color-gray2)"
                                                        placeholder="Altro..."
                                                        ></mu-text-field>
                                                </mu-form-item>
                                            </mu-form-item>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <br v-if="orderStep == 5">
                            
                            <mu-button color="var(--color-yellow)" @click="saveOrder()" v-if="orderStep < 5 && formData['order-lav-user'].length && formData['order-lav-parts'].length">Salva</mu-button>
                            <mu-button color="var(--color-green)" @click="saveOrder()" v-if="orderStep < 5 && formData['order-lav-user'].length && !formData['order-lav-parts'].length">{{ formSelectOptions['order-lav-status']['lav-status-'+ (index + 1)] }}</mu-button>
                            <mu-button color="var(--color-orange)" @click="saveOrder(true)" v-if="user.role == 'admin' && (formData['order-lav-user'].length && !formData['order-lav-parts'].length && orderStep > 0)">Step Precedente</mu-button>
                            <!--<mu-button :flat="orderStep > 0 && (formData['order-lav-user'].length != '' || formData['order-lav-parts'].length > 0)" color="red" @click="undoOrder()" v-if="orderStep < 5">Ripristina</mu-button>-->
                            
                        </mu-step-content>
                    </mu-step>
                </mu-stepper>
               
            </div>

            <div class="form-gallery">
               
                <mu-expansion-panel class="panel-order_gallery" :expand="(!formData['order-gallery'].length) ? false : true | Bool">
                    
                    <div slot="header">Galleria Ordine</div>
                    
                    <div class="form-uploads">
                        <vue-upload-multiple-image
                            idUpload="image-upload-gallery"
                            idEdit="image-edit-gallery"
                            dragText="Trascina qui gli allegati"
                            browseText="(o) cerca nel dispositivo"
                            primaryText="Copertina"
                            markIsPrimaryText="Imposta come copertina"
                            popupText="Imposta come immagine di copertina"
                            dropText="Lascia il tuo file qui ..."
                            accept="*"
                            :maxImage="15"
                            :data-images="formData['order-gallery']"
                            disabled></vue-upload-multiple-image>
                    </div>
                    
                </mu-expansion-panel>
                
                <mu-expansion-panel class="panel-order_lav_gallery" :expand="(!formData['order-lav-gallery'].length) ? false : true | Bool">
                   
                    <div slot="header">Galleria Lavorazione</div>
                    
                    <div class="form-uploads">
                        <p v-if="!formData['order-lav-user'].length && orderStep < 4" style="color: rgba(0,0,0,.6);">Selezionare un utente per caricare nuove immagini!</p>
                        <vue-upload-multiple-image
                            idUpload="image-upload-2"
                            idEdit="image-edit-2"
                            dragText="Trascina qui gli allegati"
                            browseText="(o) cerca nel dispositivo"
                            primaryText="Copertina"
                            markIsPrimaryText="Imposta come copertina"
                            popupText="Imposta come immagine di copertina"
                            dropText="Lascia il tuo file qui ..."
                            accept="*"
                            :maxImage="15"
                            @upload-success="uploadImageSuccess"
                            @before-remove="beforeRemove"
                            @edit-image="editImage"
                            @mark-is-primary="markAsPrimary"
                            @data-change="changeImages"
                            :data-images="formData['order-lav-gallery']"
                            :disabled="(formData['order-lav-user'].length && orderStep <= 4) ? false : true | Bool"
                            ></vue-upload-multiple-image>
                    </div>
                </mu-expansion-panel>
            </div>
            
            <mu-form-item class="form-foot">
                <mu-button color="var(--color-orange)" @click="viewOrder(order.id)" v-if="!this.$route.query.source">Visualizza Ordine</mu-button>
                <mu-button color="var(--color-yellow)" @click="viewHistory(order.id)">Storia</mu-button>
            </mu-form-item>
        
        </mu-form>
        
        <mu-button slot="actions" flat color="primary" @click="closeOrderDialog()">
            {{ (this.$route.query.source == 'Ordine') ? 'Torna all\'ordine' : 'Chiudi' }}
        </mu-button>
        
    </mu-dialog>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    
    @import '../assets/css/components/orderStatusDialog.css';

</style>

<script>
    
    /* eslint-disable */
    
    import VueUploadMultipleImage from 'vue-upload-multiple-image';
    
    const moment = require('moment');
    
    export default {
        name: 'OrderStatusDialog',
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
                orderStep: 0,
                formData: {
                    'order-lav-status': {
                        value: 'lav-status-0',
                        label: 'Nuovo'
                    },
                    'order-lav-user': [],
                    'order-lav-edit_date': false,
                    'order-lav-edit_oven_props': false,
                    'order-lav-parts': [],
                    'order-lav-parts_custom': '',
                    'order-gallery': [],
                    'order-lav-gallery': []
                },
                formSelectOptions: {
                    'order-lav-status': {},
                    'order-lav-user': [],
                    'order-lav-parts': []
                },
                formRules: {
                    'order-lav-status': [{ validate: (val) => !!val, message: 'Campo obbligatorio' }],
                    'order-lav-user': [{ validate: (val) => !!val, message: 'Campo obbligatorio' }],
                    'order-lav-parts_custom': [{ validate: (val) => !!val, message: '' }]
                },
                timeout: false
            }
        },
        filters: {
            Bool(value) {
                return (value) ? true : false
            }
        },
        watch: {
            $route: function() {
                if(this.$route.name == "Stato Ordine") {
                    
                    this.pageLoaded = false;
                    this.openOrderDialog();   
                } else {
                    this.orderDialogState = false;
                }
            },
            orderDialogState: function() {
                if(!this.orderDialogState && this.$route.name == 'Stato Ordine') this.$router.push({ name: 'Cruscotto Ordini' });
            }
        },
        created() {
            
            if(this.$route.name == "Stato Ordine" ) this.openOrderDialog();
            
        },
        methods: {
            openOrderDialog: function() {
                
                this.resetData();
                
                this.orderDialogState = true;
                
                this.getOptions();
                this.getFormSelectOptions();
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
                }).then( response => {
                    response.body.slug = response.body.slug.replace('order-', '');
                    $this.order = response.body;

                    $this.initOrder($this);
                });
            },
            getFormSelectOptions() {
                
                var $this = this;
                
                var fields = {
                    'order-lav-status': 'field_5c828f92a2e4e',
                    'order-lav-user': [
                        'field_5c8b62ac2c049',
                        'field_5c9b87d2937c2',
                        'field_5c9b882d937c3',
                        'field_5c9b885a937c4',
                        'field_5c9b8873937c5'
                    ],
                    'order-lav-parts': [
                        'field_5c86201e6b05e',
                        'field_5c86221c6b060'
                    ]
                };
                
                this.$http.get(this.$app.api.host +'/wp-json/wp/v2/ordini/options', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    params: {
                        field: fields
                    }
                }).then(function(response) {

                    $this.formSelectOptions = JSON.parse(response.bodyText);
                });
            },
            initOrder: function($this) {

                $this.modalTitle = ($this.order.acf['order-index'] != 0 && $this.order.acf['order-type'] == 'order') ? 'Modifica Stato Ordine #'+ $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : 'Modifica Stato Ordine #'+ $this.order.acf['order-oven_id'];

                // INTI MODAL DATA
                for(var key in $this.formData) {
                    if($this.order.acf[key]) {
                        
                        if(key == 'order-gallery' || key == 'order-lav-gallery') {

                            $this.formData[key] = (typeof $this.order.acf[key] == 'string') ? JSON.parse($this.order.acf[key]) : [];

                            if(!Array.isArray($this.formData[key])) {
                                $this.formData[key] = Object.keys($this.formData[key]).map(function(_key) {
                                    return $this.formData[key][_key];
                                });
                            }
                            
                        } else if(key == 'order-lav-parts') {
                            
                            var parts = [], i = 0;
                            for(var part in $this.order.acf[key]) {
                                parts[i] = $this.order.acf[key][part]['value'];
                                i = i + 1;
                            }
                            
                            $this.formData[key] = parts;
                            
                        } else if(key == 'order-lav-user') { 
                            
                            $this.formData[key] = []
                            
                        } else {
                            
                            $this.formData[key] = $this.order.acf[key];
                        }
                    }
                }
                
                $this.orderStep = $this.formData['order-lav-status']['value'].replace('lav-status-', '');
                $this.orderStep = parseInt(this.orderStep, 10);
                
                $this.pageLoaded = true;
            },
            undoOrder() {
                
                this.orderDialogState = false;
                this.openOrderDialog();
            },
            saveOrder: function(goback = false) {
                
                var $this = this;
                
                this.$refs.form.validate().then((result) => {
                    
                    if(result) {
                        
                        var options = {
                            emulateJSON: true
                        },
                        params = {
                            id: $this.order.id,
                            meta: {
                                'order-lav-status': $this.formData['order-lav-status'],
                                'order-lav-user': $this.formData['order-lav-user'],
                                'order-lav-parts': (typeof $this.formData['order-lav-parts'] !== 'undefined' && $this.formData['order-lav-parts'].length) ? $this.formData['order-lav-parts'] : false,
                                'order-lav-parts_custom': (typeof $this.formData['order-lav-parts_custom'] !== 'undefined' && $this.formData['order-lav-parts_custom'].length) ? $this.formData['order-lav-parts_custom'] : false
                            }
                        };
                        
                        var order_status_value = '';
                        
                        if(goback && !$this.formData['order-lav-parts'].length) {
                            
                            order_status_value = 'lav-status-'+ ($this.orderStep - 1);
                            
                            var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];
                            
                            $this.setHistory({
                                'order_id': order_id,
                                'order_status_value' : order_status_value
                            });
                            
                            $this.formData['order-lav-status']['value'] = order_status_value;
                            $this.formData['order-lav-status']['label'] = $this.formSelectOptions['order-lav-status'][order_status_value];
                            
                            params = {
                                id: $this.order.id,
                                meta: {
                                    'order-lav-status': $this.formData['order-lav-status'],
                                    'order-lav-user': $this.formData['order-lav-user']
                                }
                            };
                            
                            var order_lav_user_email = '';
                            $this.formData['order-lav-user'].forEach(function(item, index) {
                                order_lav_user_email = order_lav_user_email +''+ $this.formSelectOptions['order-lav-user'][$this.orderStep][item] +',';
                            });
                            
                            // Notifica creatore
                            $this.options['options-user-email'].forEach(function(item, index) {
                                if(item['order-user'].value == $this.order.acf['order-user'].value && $this.options['options-user-email_status'].indexOf(order_status_value) > -1) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['user-email'], 
                                        'MAM Forni - Cruscotto Ordini | Modifica Stato Ordine #'+ order_id,
                                        'Lo stato dell\'ordine #'+ order_id +' è stato retrocesso e risulta ora in: <b>'+ $this.formSelectOptions['order-lav-status'][order_status_value] +'</b><br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                            
                            // Notifica responsabili
                            var order_email_status = $this.orderStep - 2;
                            
                            if($this.formSelectOptions['order-lav-status'][order_status_value] == 'Nuovo') {
                                order_email_status = $this.orderStep - 1;
                            }
                            
                            $this.options['options-lav-status_email'].forEach(function(item, index) {
                                if(item['order-lav-status'].value == 'lav-status-'+ order_email_status) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['lav-status-email'], 
                                        'MAM Forni - Cruscotto Ordini | Modifica Stato Ordine #'+ order_id,
                                        'Lo stato dell\'ordine #'+ order_id +' è stato retrocesso e risulta ora in: <b>'+ $this.formSelectOptions['order-lav-status'][order_status_value] +'</b><br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                            
                        } else if(!$this.formData['order-lav-parts'].length) {
                            
                            order_status_value = 'lav-status-'+ ($this.orderStep + 1);
                            
                            var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];
                            
                            $this.setHistory({
                                'order_id': order_id,
                                'order_status_value' : order_status_value
                            });
                            
                            var order_lav_user_email = '';
                            $this.formData['order-lav-user'].forEach(function(item, index) {
                                order_lav_user_email = order_lav_user_email +''+ $this.formSelectOptions['order-lav-user'][$this.orderStep][item] +',';
                            });
                            
                            // Notifica creatore
                            $this.options['options-user-email'].forEach(function(item, index) {
                                if(item['order-user'].value == $this.order.acf['order-user'].value && $this.options['options-user-email_status'].indexOf(order_status_value) > -1) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['user-email'], 
                                        'MAM Forni - Cruscotto Ordini | Modifica Stato Ordine #'+ order_id,
                                        'Lo stato dell\'ordine #'+ order_id +' è stato aggiornato e risulta ora in: <b>'+ $this.formSelectOptions['order-lav-status'][order_status_value] +'</b><br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                            
                            // Notifica responsabili
                            var order_email_status = $this.orderStep + 2;
                            
                            if($this.formSelectOptions['order-lav-status'][order_status_value] == 'Spedito') {
                                order_email_status = $this.orderStep + 1;
                            }
                            
                            $this.options['options-lav-status_email'].forEach(function(item, index) {
                                if(item['order-lav-status'].value == 'lav-status-'+ order_email_status) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['lav-status-email'], 
                                        'MAM Forni - Cruscotto Ordini | Modifica Stato Ordine #'+ order_id,
                                        'Lo stato dell\'ordine #'+ order_id +' è stato aggiornato e risulta ora in: <b>'+ $this.formSelectOptions['order-lav-status'][order_status_value] +'</b><br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                            
                        } else {
                            
                            order_status_value = 'lav-status-'+ $this.orderStep;
                            
                            var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];
                            
                            $this.setHistory({
                                'order_id': order_id,
                                'order_status_value' : order_status_value
                            });
                            
                            var order_lav_user_email = '';
                            $this.formData['order-lav-user'].forEach(function(item, index) {
                                order_lav_user_email = order_lav_user_email +''+ $this.formSelectOptions['order-lav-user'][$this.orderStep][item] +',';
                            });
                            
                            // Notifica creatore
                            $this.options['options-user-email'].forEach(function(item, index) {
                                if(item['order-user'].value == $this.order.acf['order-user'].value && $this.options['options-user-email_status'].indexOf(order_status_value) > -1) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['user-email'], 
                                        'MAM Forni - Cruscotto Ordini | Materiale mancante per l\'ordine #'+ order_id,
                                        'È stata segnalata la mancanza di materiale per l\'ordine #'+ order_id +'<br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                            
                            // Notifica responsabili
                            $this.options['options-lav-status_email'].forEach(function(item, index) {
                                if(item['order-lav-status'].value == order_status_value) {
                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it', 
                                        item['lav-status-email'], 
                                        'MAM Forni - Cruscotto Ordini | Materiale mancante per l\'ordine #'+ order_id,
                                        'È stata segnalata la mancanza di materiale per l\'ordine #'+ order_id +'<br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                    );
                                }
                            });
                        }
                        
                        $this.formData['order-lav-status']['value'] = order_status_value;
                        $this.formData['order-lav-status']['label'] = $this.formSelectOptions['order-lav-status'][order_status_value];
                        
                        $this.pageLoaded = false;
                        
                        $this.$http.post($this.$app.api.host +'/wp-json/wp/v2/ordini/update-status', params, options).then(function(response) {
                            
                            $this.$emit('return', {
                                reloadOrder: true,
                                message: 'Ordine aggiornato con successo!',
                                status: response.ok
                            });
                            
                            if(!$this.formData['order-lav-parts'].length) {
                                
                                order_status_value = 'lav-status-'+ ($this.orderStep + 1);
                            
                                var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];
                                
                            } else {

                                order_status_value = 'lav-status-'+ $this.orderStep;

                                var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                            }
                            
                            $this.formData = {
                                'order-lav-status': {
                                    value: 'lav-status-0',
                                    label: 'Nuovo'
                                },
                                'order-lav-user': [],
                                'order-lav-edit_date': false,
                                'order-lav-edit_oven_props': false,
                                'order-lav-parts': [],
                                'order-lav-parts_custom': ''
                            };
                            
                            $this.closeOrderDialog();

                        }, function(error) {

                            $this.$emit('return', {
                                reloadOrder: false,
                                message: 'Impossibile aggiornare l\'ordine! (#'+ error.status +')',
                                status: error.ok
                            });
                            
                            $this.getOrderData();

                        });
                    }
                });
            },
            setHistory: function(data) {
                
                var $this = this,
                    updated_meta = false;
                            
                for(var key in $this.formData) {

                    var _old = '',
                        _old_value = '',
                        _new = '',
                        _new_value = '';

                    if(key == 'order-lav-user') {

                    } else if(key == 'order-lav-status') {

                        _old       = $this.order.acf[key].value;
                        _old_value = $this.order.acf[key];

                        _new       = data['order_status_value'];
                        _new_value = {
                            label: $this.formSelectOptions[key][data['order_status_value']],
                            value: data['order_status_value']
                        }

                    } else if(key == 'order-lav-parts') {

                        if($this.order.acf[key]) {
                            _old_value = $this.order.acf[key];
                            $this.order.acf[key].forEach(function(el, index) {
                                _old = _old +'|'+ $this.order.acf[key][index].value;
                            });
                        } else {
                            _old_value = false;
                        }
                        
                        _new_value = [];
                        $this.formData[key].forEach(function(el, index) {
                            _new_value[index] = {
                                value: el,
                                label: $this.formSelectOptions[key][$this.orderStep][el]
                            }
                        });
                        
                        $this.formData[key].forEach(function(el, index) {
                            _new = _new +'|'+ $this.formData[key][index];
                        });

                    } else {

                        _old_value = _old = ($this.order.acf[key]) ? $this.order.acf[key] : false;
                        _new_value = _new = ($this.formData[key]) ? $this.formData[key] : false;
                    }

                    if(_old != _new) {

                        updated_meta = (!updated_meta) ? {} : updated_meta;

                        updated_meta[key] = {
                            'old': _old_value,
                            'new': _new_value
                        }
                    }
                }

                if(updated_meta !== false) {

                    var new_order_lav_user = [],
                        index = ($this.orderStep <= 1) ? 0 : $this.orderStep;
                    
                    $this.formData['order-lav-user'].forEach(function(el, index) {
                        new_order_lav_user[index] = {
                            value: el,
                            label: $this.formSelectOptions['order-lav-user'][$this.orderStep][el]
                        }
                    });
                    
                    var nextOrderStep = parseInt(data['order_status_value'].replace('lav-status-',''));
                    var action = '';
                    if($this.orderStep == nextOrderStep) {
                        action = 'edit-status_0';
                    } else if($this.orderStep < nextOrderStep) {
                        action = 'edit-status_1';
                    } else if($this.orderStep > nextOrderStep) {
                        action = 'edit-status_2';
                    }

                    $this.updateHistory($this.order.id, {
                        id: $this.order.id,
                        user: $this.user,
                        date: moment().format(),
                        action: action,
                        data: {
                            'order-oven_id': data['order_id'],
                            'order-lav-user': new_order_lav_user,
                            'updated': updated_meta
                        }
                    });
                }
                
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
            viewOrder: function(order_id) {
                
                this.$router.push({ name: 'Ordine', params: { action: 'view', orderData: order_id }, query: {source: 'Stato Ordine'} });
            },
            viewHistory: function(order_id) {
                
                if(this.$route.query.source) {
                    this.$router.push({ name: 'Storia Ordine', params: { orderData: order_id }, query: { source: this.$route.query.source } });
                } else {
                    this.$router.push({ name: 'Storia Ordine', params: { orderData: order_id } });
                }
            },
            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },
            toggleFlag: function(field) {
                
                this.formData[field] = !this.formData[field];
                
                var $this = this,
                    headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    options = {
                        headers: headers,
                        emulateJSON: true
                    },
                    params = {
                        id: this.order.id,
                        field: field,
                        value: this.formData[field]
                    };
                
                this.$http.post(this.$app.api.host +'/wp-json/wp/v2/ordini/toggle-flag', params, options).then(function(response) {    
                    
                    if(response.body) {
                        
                        var updateHistory_value = {
                                id: $this.order.id,
                                user: $this.user,
                                date: moment().format(),
                                action: 'toggle_flag-'+ field,
                                data: {
                                    'order-oven_id': $this.order.acf['order-oven_id'],
                                }
                            };
                        updateHistory_value.data[field] = $this.formData[field];
                        $this.updateHistory($this.order.id, updateHistory_value);
                            
                        if(this.formData[field]) {
                            

                            $this.$emit('return', {
                                reloadOrder: true,
                                message: 'Flag ATTIVO!',
                                status: response.ok
                            });

                        } else {
                            
                            $this.updateHistory($this.order.id, {
                                id: $this.order.id,
                                user: $this.user,
                                date: moment().format(),
                                action: 'mark-change-out-'+ field,
                                data: {
                                    'order-oven_id': $this.order.acf['order-oven_id']
                                }
                            });

                            $this.$emit('return', {
                                reloadOrder: true,
                                message: 'Flag DISATTIVO!',
                                status: response.ok
                            });
                        }
                    } else {
                        
                        $this.formData[field] = $this.order.acf[field];
                    
                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Abbiamo riscontrato un problema!',
                            status: false
                        });
                    }
                    
                }, function(error) {
                    
                    $this.formData[field] = $this.order.acf[field];
                    
                    $this.$emit('return', {
                        reloadOrder: false,
                        message: 'Abbiamo riscontrato un problema! (#'+ error.status +')',
                        status: error.ok
                    });
                });
            },
            updateHistory: function(order_id, data) {
                
                var $this = this,
                    options = {
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        emulateJSON: true
                    },
                    params = {
                        id: order_id,
                        data: data
                    };
                
                this.$http.post(this.$app.api.host +'/wp-json/wp/v2/ordini/update-history', params, options);
                
            },
            deleteGalleryImages: function(index) {
                
                var options = {
                        emulateJSON: true
                    },
                    params = {
                        id: this.order.id,
                        field: 'order-lav-gallery',
                        images: this.formData['order-lav-gallery'],
                        index: index
                    };
                
                return this.$http.post(this.$app.api.host +'/wp-json/wp/v2/ordini/delete-gallery-images', params, options);
            },
            uploadImageSuccess: function(formData, index, fileList) {
                var $this = this;

                this.formData['order-lav-gallery'] = fileList;
                
                clearTimeout(this.timeout);
                this.timeout = setTimeout(function() {
                    
                    clearTimeout($this.timeout);
                    $this.timeout = false;
                    
                    var options = {
                            emulateJSON: true
                        },
                        params = {
                            id: $this.order.id,
                            'order-title': $this.order.title.rendered,
                            meta: {
                                'order-type': $this.order.acf['order-type'],
                                'order-oven_id': $this.order.acf['order-oven_id'],
                                'order-lav-user': $this.formData['order-lav-user'],
                                'order-lav-gallery': $this.formData['order-lav-gallery']
                            }
                        };
                    
                    $this.$emit('return', {
                        reloadOrder: false,
                        message: 'Caricamento immagine in corso',
                        status: 'warning',
                        permanent: true
                    });

                    $this.$http.post($this.$app.api.host +'/wp-json/wp/v2/ordini/update', params, options).then( response => {
                        var order_lav_user_email = '';
                        $this.formData['order-lav-user'].forEach(function(item, index) {
                            order_lav_user_email = order_lav_user_email +''+ $this.formSelectOptions['order-lav-user'][$this.orderStep][item] +',';
                        });

                        // Notifica creatore
                        var order_status_value = 'lav-status-'+ $this.orderStep;
                        var order_id = (response.body.meta['order-index'] > 0) ? response.body.meta['order-oven_id'] +'-'+ response.body.meta['order-index'] : response.body.meta['order-oven_id'];

                        $this.options['options-user-email'].forEach(function(item, index) {
                            if(item['order-user'].value == $this.order.acf['order-user'].value) {
                                $this.sendMail(
                                    $this,
                                    'noreply@mamforni.it', 
                                    item['user-email'], 
                                    'MAM Forni - Cruscotto Ordini | Nuove immagini per Ordine #'+ order_id,
                                    'Sono state inserite delle nuove fotografie per l\'ordine #'+ order_id +'<br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                );
                            }
                        });

                        var new_order_lav_user = [];
                        $this.formData['order-lav-user'].forEach(function(el, index) {
                            new_order_lav_user[index] = {
                                value: el,
                                label: $this.formSelectOptions['order-lav-user'][$this.orderStep][el]
                            }
                        });

                        //NB: questo lo faccio per aggiornare correttamente l'ordine
                        // Non si sa perchè ma la prima volta che si fa il get dopo averlo modificato restituisce la galleria vecchia
                        $this.$http.get($this.$app.api.host +'/wp-json/wp/v2/ordini/'+$this.$route.params.orderData, {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        }).then( response => {
                            console.log(response.body.acf['order-lav-gallery']);
                        });

                        /*
                        $this.updateHistory(response.body.id, {
                            id: response.body.id,
                            user: $this.user,
                            date: moment().format(),
                            action: 'edit',
                            data: {
                                'order-oven_id': order_id,
                                'order-lav-user': new_order_lav_user,
                                'updated': {
                                    'order-lav-gallery': {
                                        'old' : $this.order.acf['order-lav-gallery'],
                                        'new' : JSON.parse(response.body.meta['order-lav-gallery'])
                                    }
                                }
                            }
                        });
                        */

                    }, function(error) {

                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Impossibile caricare le immagini! (#'+ error.status +')',
                            status: error.ok
                        });

                    });
                    
                }, fileList.length * 500);
            },
            changeImages: function(data) {
                
            },
            beforeRemove: function(index, done, fileList) {
                
                var $this = this;
                    
                this.deleteGalleryImages(index).then(function(response) {
                    if(!response.body.exist) {

                        $this.formData['order-lav-gallery'] = fileList;

                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Errore, l\'immagine non esiste!',
                            status: false
                        });

                        return false;

                    } else if(response.body.exist && response.body.return) {

                        $this.formData['order-lav-gallery'] = fileList;

                        var order_lav_user_email = '';
                        $this.formData['order-lav-user'].forEach(function(item, index) {
                            order_lav_user_email = order_lav_user_email +''+ $this.formSelectOptions['order-lav-user'][$this.orderStep][item] +',';
                        });

                        // Notifica creatore
                        var order_status_value = 'lav-status-'+ $this.orderStep;
                        var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] +'-'+ $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                        /*
                        $this.options['options-user-email'].forEach(function(item, index) {
                            if(item['order-user'].value == $this.order.acf['order-user'].value) {
                                $this.sendMail(
                                    $this,
                                    'noreply@mamforni.it',
                                    item['user-email'],
                                    'MAM Forni - Cruscotto Ordini | Immagine eliminata per Ordine #'+ order_id,
                                    'Sono state rimosse alcune fotografie per l\'ordine #'+ order_id +'<br><br><b>Creato da:</b> '+ $this.order.acf['order-user'].label +'<br><b>Aggiornato da:</b> '+ order_lav_user_email +'<br><br><br>Link: '+ $this.$app.urls.home +'/#/cruscotto/ordine/'+ $this.order.id +'<br>Storia: '+ $this.$app.urls.home +'/#/cruscotto/storia/'+ $this.order.id
                                );
                            }
                        });
                        */

                        var new_order_lav_user = [];
                        $this.formData['order-lav-user'].forEach(function(el, index) {
                            new_order_lav_user[index] = {
                                value: el,
                                label: $this.formSelectOptions['order-lav-user'][$this.orderStep][el]
                            }
                        });

                        //NB: questo lo faccio per aggiornare correttamente l'ordine
                        // Non si sa perchè ma la prima volta che si fa il get dopo averlo modificato restituisce la galleria vecchia
                        $this.$http.get($this.$app.api.host +'/wp-json/wp/v2/ordini/'+$this.$route.params.orderData, {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        }).then( response => {
                            console.log(response.body.acf['order-lav-gallery']);
                        });

                        /*
                        $this.updateHistory($this.order.id, {
                            id: $this.order.id,
                            user: $this.user,
                            date: moment().format(),
                            action: 'edit',
                            data: {
                                'order-oven_id': order_id,
                                'order-lav-user': new_order_lav_user,
                                'updated': {
                                    'order-lav-gallery': {
                                        'old' : $this.order.acf['order-lav-gallery'],
                                        'new' : $this.formData['order-lav-gallery']
                                    }
                                }
                            }
                        });
                        */

                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Immagine eliminata con successo!',
                            status: true
                        });

                        done();

                    } else {

                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Impossibile eliminare l\'immagine!',
                            status: false
                        });

                        return false;
                    }

                }, function(error) {

                    $this.$emit('return', {
                        reloadOrder: false,
                        message: 'Impossibile eliminare l\'immagine! (#'+ error.status +')',
                        status: false
                    });
                    
                    return false;
                });
            },
            editImage: function(formData, index, fileList) {
                
            },
            markAsPrimary: function() {
                
            },
            inArray: function (array, value) {
                return array.indexOf(value) > -1 ? true : false;
            }
        },
        components: {
            VueUploadMultipleImage
        }
    }

</script>