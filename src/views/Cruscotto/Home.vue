<template>
    <div class="home">
        <div class="page-wrapper">

            <div class="loader" v-if="!pageLoaded">
                <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
            </div>

            <div v-if="pageLoaded" style="width: 100%;">

                <OrderStatusDialog :user="user" @return="openSnackBar" />
                <OrderHistoryDialog :user="user" @return="openSnackBar" />
                <OrderStatusFilterDialog :open="orderStatusFilterDialogState" :sortData="sort" :filterData="filter" @return="closeOrderStatusFilterDialog" />
                
                <p style="text-align: right">
                    <mu-button class="btn-print print-none" color="var(--color-orange)" textColor="var(--color-white)" @click="print()" small>
                        <mu-icon left value="print"></mu-icon>STAMPA
                    </mu-button>
                </p>

                <div class="tools">
                    <div class="filter-wrap">

                        <mu-chip class="filter filter-all" :class="{ 'active': !currOrdersLavStatus }" @click="applyFilter('order-lav-status', '')">
                            TUTTI
                            <mu-badge :content="(ordersCounter['*'] !== 'undefined') ? String(ordersCounter['*']) : '--'" circle color="var(--color-yellow)"></mu-badge>
                        </mu-chip>

                        <mu-chip v-for="(status, index) in ordersLavStatus" :key="'order-status'+ index" class="filter" :class="{ 'active': currOrdersLavStatus == status.value }" @click="applyFilter('order-lav-status', status.value)">
                            {{ status.label }}
                            <mu-badge :content="(ordersCounter[status.value] !== 'undefined') ? String(ordersCounter[status.value]) : '--'" circle color="var(--color-yellow)"></mu-badge>
                        </mu-chip>
                    </div>

                    <mu-button :class="['settings', 'print-none', { 'active': isFilter || isSort }]" @click="openOrderStatusFilterDialog()" flat>
                        <mu-icon value="filter_list" :color="(isFilter || isSort) ? 'var(--color-yellow)' : 'var(--color-orange)'"></mu-icon>
                    </mu-button>

                    <div class="switchmode-wrap print-none">
                        <mu-button class="switchmode" :class="{ 'active': linkMode == 0 }" @click="linkMode = 0" large>Stato</mu-button>
                        <mu-button class="switchmode" :class="{ 'active': linkMode == 1 }" @click="linkMode = 1" large>Storia</mu-button>
                    </div>
                </div>

                <div class="orders-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 90px;">STATO</th>
                                <th style="width: 80px;">#</th>
                                <th style="width: 100px;">CONSEGNA</th>
                                <th>
                                    <ul class="content-big">
                                        <li style="width: 100px;">TIPO</li>
                                        <li style="width: 50px;">DIAM.</li>
                                        <li style="width: 80px;">FINITURA</li>
                                        <li style="width: 150px;">BOCCA</li>
                                        <li style="width: 150px;">ALIM.</li>
                                        <li style="width: 80px;">LATO</li>
                                    </ul>
                                    <span class="content-small">DESCRIZIONE</span>
                                </th>
                                <th style="width: 130px;">ATTRIBUTI</th>
                                <th style="width: 120px;">FLAG</th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr v-for="(currOrder, index) in ordersData" :key="'order-'+ index" :class="{ 'priority': !currOrder.acf['order-transport'] && isPriorityOrder(currOrder) }">
                                <td style="width: 90px;">{{ (currOrder.acf['order-lav-status']) ? currOrder.acf['order-lav-status'].label : 'Nuovo' }}</td>
                                <td style="width: 80px;">{{ currOrder.acf['order-oven_id'] }}{{ (currOrder.acf['order-index']  > 0) ? '-'+ currOrder.acf['order-index'] : '' }}</td>
                                <td style="width: 100px;">{{ currOrder.acf['order-date'] | moment('DD/MM/YY') }}</td>
                                <td>
                                    <ul class="content-big">
                                        <li style="width: 100px;">{{ currOrder.acf['order-oven_model'].label }}</li>
                                        <li style="width: 50px;">{{ currOrder.acf['order-oven_diameter'].label }}</li>
                                        <li style="width: 80px;">{{ currOrder.acf['order-oven_topcoat'].label }}</li>
                                        <li style="width: 150px;">{{ currOrder.acf['order-oven_mouth'].label }}</li>
                                        <li style="width: 150px;">{{ currOrder.acf['order-oven_fuel'].label }}</li>
                                        <li style="width: 80px;">{{ currOrder.acf['order-oven_fuel_side'].label }}</li>
                                    </ul>
                                    <ul class="content-small">
                                        <li>
                                            <span><b>Modello:</b> {{ currOrder.acf['order-oven_model'].label }}</span>
                                            <span><b>Diam.:</b> {{ currOrder.acf['order-oven_diameter'].label }}</span>
                                        </li>
                                        <li>
                                            <span><b>Finitura:</b> {{ currOrder.acf['order-oven_topcoat'].label }}</span>
                                            <span><b>Bocca:</b> {{ currOrder.acf['order-oven_mouth'].label }}</span>
                                        </li>
                                        <li>
                                            <span><b>Alimentazione:</b> {{ currOrder.acf['order-oven_fuel'].label }}</span>
                                            <span><b>Lato:</b> {{ currOrder.acf['order-oven_fuel_side'].label }}</span>
                                        </li>
                                        <li><span><b>ETL:</b> {{ (currOrder.acf['order-oven_etl']) ? 'SI' : 'NO' }}</span></li>
                                    </ul>
                                </td>
                                <td class="td-icons-wrap" style="width: 130px;">
                                    <div class="td-icons">
                                        <mu-tooltip :content="currOrder.acf['order-packing'].label">
                                            <img :src="getOrderPropIcon(currOrder, 'order-packing')" alt="">
                                        </mu-tooltip>
                                        <mu-tooltip content="Trasporto a ns. carico">
                                            <img src="../../assets/images/icons/trasporto.png" v-if="currOrder.acf['order-transport']">
                                        </mu-tooltip>
                                    </div>
                                </td>
                                <td class="td-icons-wrap" style="width: 120px;">
                                    <div class="td-icons" v-if="(currOrder.acf['order-lav-parts'] && currOrder.acf['order-lav-parts'].length) || currOrder.acf['order-lav-edit_oven_props'] || currOrder.acf['order-lav-edit_date'] || currOrder.acf['order-lav-edit_note'] || currOrder.acf['order-lav-edit_transport']">
                                        <mu-tooltip content="Materiale mancante">
                                            <img src="../../assets/images/icons/alert.png" v-if="currOrder.acf['order-lav-parts'] && currOrder.acf['order-lav-parts'].length">
                                        </mu-tooltip>
                                        <mu-tooltip content="Modifica dati tecnici">
                                            <img src="../../assets/images/icons/edit-oven_props.png" v-if="currOrder.acf['order-lav-edit_oven_props']">
                                        </mu-tooltip>
                                        <mu-tooltip content="Modifica data di consegna">
                                            <img src="../../assets/images/icons/edit-date.png" v-if="currOrder.acf['order-lav-edit_date']">
                                        </mu-tooltip>
                                        <mu-tooltip content="Modifica note">
                                            <img src="../../assets/images/icons/edit-note.png" v-if="currOrder.acf['order-lav-edit_note']">
                                        </mu-tooltip>
                                        <mu-tooltip content="Modifica trasporto">
                                            <img src="../../assets/images/icons/edit-transport.png" v-if="currOrder.acf['order-lav-edit_transport']">
                                        </mu-tooltip>
                                    </div>
                                </td>
                                <mu-ripple @click="openOrderStatusDialog(currOrder)" color="var(--color-gray2)" :opacity="0.1"></mu-ripple>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <mu-snackbar color="success" :open="pageLoaded && !ordersLoaded" position="bottom-end" style="min-width: 0px;">
                <mu-circular-progress :size="20" color="var(--color-white)" style="margin-right: 10px;"></mu-circular-progress>
                AGGIORNO...
            </mu-snackbar>

            <mu-snackbar :color="(typeof snackBar.return === 'boolean') ? (snackBar.return) ? 'success' : 'error' : snackBar.return" :open.sync="snackBar.open">
                <mu-icon left :value="(snackBar.return) ? 'check_circle' : 'warning'"></mu-icon>
                {{snackBar.message}}
                <mu-button flat slot="action" color="#fff" @click="closeSnackBar()">CHIUDI</mu-button>
            </mu-snackbar>

        </div>
    </div>
</template>

<style scoped>
    @import '../../assets/css/views/cruscotto-home.css';

</style>

<script>
    /* eslint-disable */

    import OrderStatusDialog from '@/components/OrderStatusDialog.vue'
    import OrderHistoryDialog from '@/components/OrderHistoryDialog.vue'
    import OrderStatusFilterDialog from '@/components/OrderStatusFilterDialog.vue'

    const moment = require('moment');

    export default {
        name: 'CruscottoHome',
        data() {
            return {
                user: this.$app.user,
                pageLoaded: false,
                ordersLoaded: false,
                reloadInterval: false,
                linkMode: 0,
                ordersCounter: {},
                isSort: false,
                isFilter: false,
                currOrdersLavStatus: '',
                ordersLavStatus: [],
                sort: {
                    'sort-field': 'order-date',
                    'sort-type': 'ASC'
                },
                filter: {
                    'order-oven_model': [],
                    'order-oven_diameter': [],
                    'order-oven_topcoat': [],
                    'order-oven_mouth': [],
                    'order-oven_fuel': [],
                    'order-oven_fuel_side': [],
                    'order-oven_sill': '',
                    'order-oven_etl': '',
                    'order-date': {
                        age: '',
                        start: '',
                        end: ''
                    }
                },
                orderStatusFilterDialogState: false,
                options: {},
                orders: {},
                ordersData: {},
                snackBar: {
                    open: false,
                    timer: false,
                    message: '',
                    return: false
                }
            }
        },
        watch: {
            $route: function() {

                if (this.$route.name == 'Cruscotto Ordini') {

                    this.reloadInterval = false;
                    this.getOptions();

                } else {

                    clearInterval(this.reloadInterval);
                    this.ordersLoaded = true;
                }
            },
            currOrdersLavStatus: function(val, preVal) {

                this.applyFilter('order-lav-status', val);
            }
        },
        beforeDestroy() {
            clearInterval(this.reloadInterval);
            this.ordersLoaded = true;
        },
        created() {

            var $this = this;

            this.getOptions();
            this.getCurrentUser();
            this.getOrdersStatus();
            this.getOrders();
        },
        methods: {
            getCurrentUser: function() {

                if (this.$session.exists()) {
                    this.$app.user = this.$session.get('user');
                }
            },
            getOptions: function() {

                var $this = this;

                this.$http.get(this.$app.api.host + '/wp-json/acf/v3/options/ordini_options', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(function(response) {

                    $this.options = response.body.acf;

                    $this.options['options-lav-priority_days'] = parseInt($this.options['options-lav-priority_days']);
                    $this.options['options-updating_time'] = parseInt($this.options['options-updating_time']);

                    if ($this.$route.name == 'Cruscotto Ordini' && !$this.reloadInterval) {
                        $this.reloadInterval = setInterval(function() {

                            $this.ordersLoaded = false;
                            $this.getOrders();

                        }, $this.options['options-updating_time']);
                    }
                });
            },
            getOrdersStatus() {

                var $this = this;

                this.$http.get(this.$app.api.host + '/wp-json/wp/v2/ordini/options', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    params: {
                        field: 'field_5c828f92a2e4e'
                    }
                }).then(function(response) {

                    var response = response.body;

                    $this.ordersCounter['*'] = 0;
                    Object.keys(response).map(function(key, index) {
                        $this.ordersLavStatus.push({
                            value: key,
                            label: response[key]
                        });
                        $this.ordersCounter[key] = 0;
                    });
                });

            },
            getOrders: function(date = false) {

                var $this = this;

                var parametri = {
                        'post_type': 'ordini',
                        'posts_per_page' : -1,
                        'no_found_rows' : 'true',
                        'update_post_term_cache' : 'false',
                        'update_post_meta_cache' :'false',
                        'cache_results' : 'false',
                        'meta_key' : 'order-date',
                        'orderby' : 'meta_value',
                        'order' : 'ASC',
                        'meta_query': [{
                            'key': 'order-type',
                            'value': 'order',
                            'compare': '=',
                            'type': 'CHAR'
                        }]
                    },
                    opzioni = {
                        'fields': [
                            'ID'
                        ],
                        'meta': [
                            'order-oven_id',
                            'order-index',
                            'order-date',
                            'order-oven_model',
                            'order-oven_diameter',
                            'order-oven_topcoat',
                            'order-oven_mouth',
                            'order-oven_fuel',
                            'order-oven_fuel_side',
                            'order-oven_sill',
                            'order-oven_etl',
                            'order-packing',
                            'order-transport',
                            'order-lav-status',
                            'order-lav-parts',
                            'order-lav-edit_date',
                            'order-lav-edit_oven_props',
                            'order-lav-edit_note',
                            'order-lav-edit_transport'
                        ]
                    };

                if (!this.ordersLoaded) {
                    this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                        // headers: headers,
                        'data' : {
                            'parametri' : parametri,
                            'opzioni' : opzioni,
                            'chiave' : 'cruscotto'
                        }

                    }).then(function(response) {

                        $this.ordersLoaded = true;
                        $this.pageLoaded = true;

                        response.body.filter(function(order) {
                            if (!order.acf['order-lav-status']) {
                                order.acf['order-lav-status'] = {
                                    value: 'lav-status-0',
                                    label: 'Nuovo',
                                };
                            }
                        });

                        $this.ordersData = $this.orders = response.body;

                        $this.applyFilter('order-lav-status', $this.currOrdersLavStatus);

                    });
                }
            },
            getOrderPropIcon: function(order, prop) {

                var images = require.context('../../assets/images/icons/', false, /\.png$/),
                    image = '';

                if (typeof order.acf[prop] == 'object') image = order.acf[prop].value;
                else image = prop;

                return images('./' + image + ".png");
            },
            isPriorityOrder: function(order) {

                if (order.acf['order-lav-status'].value != this.ordersLavStatus[this.ordersLavStatus.length - 1].value &&
                    order.acf['order-lav-status'].value != 'lav-status-4' &&
                    moment(order.acf['order-date']).diff(moment(), 'days') <= this.options['options-lav-priority_days']) {
                    return true;
                }

                return false;
            },
            applyFilter: function(prop, value) {

                var $this = this;

                if (prop == 'order-lav-status') this.currOrdersLavStatus = value;
                else if (prop == '*') this.filter = value;
                else this.filter[prop] = value;

                $this.ordersCounter['*'] = 0;
                Object.keys($this.ordersCounter).forEach(function(key) {
                    $this.ordersCounter[key] = 0
                });

                this.ordersData = this.orders.filter(function(order) {
                    
                    var checkLavStatus = (!$this.currOrdersLavStatus && order.acf['order-lav-status'].value != 'lav-status-5' || order.acf['order-lav-status'].value == $this.currOrdersLavStatus) ? true : false;
                    var checkOvenModel = (!$this.filter['order-oven_model'].length || $this.filter['order-oven_model'].indexOf(order.acf['order-oven_model'].value) > -1) ? true : false;
                    var checkOvenDiameter = (!$this.filter['order-oven_diameter'].length || $this.filter['order-oven_diameter'].indexOf(order.acf['order-oven_diameter'].value) > -1) ? true : false;
                    var checkOvenTopcoat = (!$this.filter['order-oven_topcoat'].length || $this.filter['order-oven_topcoat'].indexOf(order.acf['order-oven_topcoat'].value) > -1) ? true : false;
                    var checkOvenMouth = (!$this.filter['order-oven_mouth'].length || $this.filter['order-oven_mouth'].indexOf(order.acf['order-oven_mouth'].value) > -1) ? true : false;
                    var checkOvenFuel = (!$this.filter['order-oven_fuel'].length || $this.filter['order-oven_fuel'].indexOf(order.acf['order-oven_fuel'].value) > -1) ? true : false;
                    var checkOvenFuelSide = (!$this.filter['order-oven_fuel_side'].length || $this.filter['order-oven_fuel_side'].indexOf(order.acf['order-oven_fuel_side'].value) > -1) ? true : false;
                    var checkOvenSill = ($this.filter['order-oven_sill'] === '' || ($this.filter['order-oven_sill'] == order.acf['order-oven_sill'])) ? true : false;
                    var checkOvenEtl = ($this.filter['order-oven_etl'] === '' || ($this.filter['order-oven_etl'] == order.acf['order-oven_etl'])) ? true : false;

                    var orderDate = moment(order.acf['order-date']).format('YYYY-MM-DD'),
                        filterStartDate = '',
                        filterEndDate = '',
                        checkDate = false;

                    if ($this.filter['order-date'].age == 'custom') {
                        filterStartDate = ($this.filter['order-date'].start) ? moment($this.filter['order-date'].start).format('YYYY-MM-DD') : orderDate;
                        filterEndDate = ($this.filter['order-date'].end) ? moment($this.filter['order-date'].end).format('YYYY-MM-DD') : orderDate;
                    } else {
                        filterStartDate = ($this.filter['order-date'].start) ? moment($this.filter['order-date'].start, 'DD/MM/YYYY').format('YYYY-MM-DD') : orderDate;
                        filterEndDate = ($this.filter['order-date'].end) ? moment($this.filter['order-date'].end, 'DD/MM/YYYY').format('YYYY-MM-DD') : orderDate;
                    }

                    var checkDate = moment(orderDate).isBetween(filterStartDate, filterEndDate, null, '[]');

                    if (checkOvenModel && checkOvenDiameter && checkOvenTopcoat && checkOvenMouth && checkOvenFuel && checkOvenFuelSide && checkOvenSill && checkOvenEtl && checkDate) {
                        $this.ordersCounter['*'] = $this.ordersCounter['*'] + 1;
                        $this.ordersCounter[order.acf['order-lav-status'].value] = $this.ordersCounter[order.acf['order-lav-status'].value] + 1;
                    }
                    
                    return checkLavStatus && checkOvenModel && checkOvenDiameter && checkOvenTopcoat && checkOvenMouth && checkOvenFuel && checkOvenFuelSide && checkOvenSill && checkOvenEtl && checkDate
                    
                });
                
                this.ordersData = this.ordersData.sort(function(a, b) {

                    var a_date = '',
                        b_date = '';
                    
                    if($this.sort['sort-field'] == 'order-date') {
                        a_date = parseInt(moment(a.acf['order-date']).format('YYYYMMDD') +''+ a.acf['order-oven_id'] +''+ a.acf['order-index']);
                        b_date = parseInt(moment(b.acf['order-date']).format('YYYYMMDD') +''+ b.acf['order-oven_id'] +''+ a.acf['order-index']);
                    } else if($this.sort['sort-field'] == 'post_date_gmt') {
                        a_date = parseInt(moment(a['post_date_gmt']).format('YYYYMMDD') +''+ a.acf['order-oven_id'] +''+ a.acf['order-index']);
                        b_date = parseInt(moment(b['post_date_gmt']).format('YYYYMMDD') +''+ b.acf['order-oven_id'] +''+ a.acf['order-index']);
                    }
                    
                    return a_date - b_date
                });
                
                if(this.sort['sort-type'] == 'DESC' || this.currOrdersLavStatus == 'lav-status-5') {
                    this.ordersData = this.ordersData.reverse();
                }
            },
            countOrders: function(status) {

                var count = 0;

                /*
                this.orders.inlate.forEach(function(order, index) {
                    if (!('order-lav-status' in order.acf)) {
                        order.acf['order-lav-status'] = {
                            'value': 'lav-status-0',
                            'label': 'Nuovo'
                        };
                    }
                    count = (order.acf['order-lav-status'].value == 'lav-status-' + status) ? count + 1 : count;
                });

                this.orders.new.forEach(function(order, index) {
                    if (!('order-lav-status' in order.acf)) {
                        order.acf['order-lav-status'] = {
                            'value': 'lav-status-0',
                            'label': 'Nuovo'
                        };
                    }
                    count = (order.acf['order-lav-status'].value == 'lav-status-' + status) ? count + 1 : count;
                });

                if (status == this.ordersLavStatus.length - 1) {
                    count = count + this.orders.old.length;
                }
                */

                return String(count);
            },
            openOrderStatusFilterDialog: function() {

                clearInterval(this.reloadInterval);
                this.ordersLoaded = true;

                this.orderStatusFilterDialogState = true;
            },
            closeOrderStatusFilterDialog: function(data) {
                
                if(data.filterData['order-oven_sill'] == 'true') data.filterData['order-oven_sill'] = true;
                else if(data.filterData['order-oven_sill'] == 'false') data.filterData['order-oven_sill'] = false;
                
                if(data.filterData['order-oven_etl'] == 'true') data.filterData['order-oven_etl'] = true;
                else if(data.filterData['order-oven_etl'] == 'false') data.filterData['order-oven_etl'] = false;

                if (data.filterData['order-oven_model'].length || data.filterData['order-oven_diameter'].length || data.filterData['order-oven_topcoat'].length || data.filterData['order-oven_mouth'].length || data.filterData['order-oven_fuel'].length || data.filterData['order-oven_fuel_side'].length || data.filterData['order-oven_sill'] != '' || data.filterData['order-oven_etl'] != '' || data.filterData['order-date'].age || data.filterData['order-date'].start || data.filterData['order-date'].end) {
                    this.isFilter = true;
                } else {
                    this.isFilter = false;
                }
                
                this.sort = data.sortData;
                if(data.sortData['sort-field'] != 'order-date' || data.sortData['sort-type'] != 'ASC') {
                    this.isSort = true;
                } else {
                    this.isSort = false;
                }
                
                this.applyFilter('*', data.filterData);

                this.reloadInterval = false;
                this.getOptions();

                this.orderStatusFilterDialogState = false;
            },
            openOrderStatusDialog: function(order) {

                if (this.linkMode == 0) {
                    this.$router.push({
                        name: 'Stato Ordine',
                        params: {
                            orderData: order.ID
                        }
                    });
                } else if (this.linkMode == 1) {
                    this.$router.push({
                        name: 'Storia Ordine',
                        params: {
                            orderData: order.ID
                        }
                    });
                }

            },
            openSnackBar: function(response) {

                this.snackBar.open = true;

                if(!response.permanent) {
                    if (this.snackBar.timer) clearTimeout(this.snackBar.timer);
                    this.snackBar.timer = setTimeout(() => {

                        this.snackBar.open = false;

                    }, 3000);
                }
                
                this.snackBar.message = response.message;
                this.snackBar.return = response.status;

                if (response.reloadOrder) {
                    this.ordersLoaded = false;
                    this.getOrders();
                }
            },
            closeSnackBar: function() {

                if (this.snackBar.timer) clearTimeout(this.snackBar.timer);
                this.snackBar.open = false;
            },
            print: function() {
                window.print()
            }
        },
        components: {
            OrderStatusDialog,
            OrderHistoryDialog,
            OrderStatusFilterDialog
        }
    }

</script>
