<template>
    <mu-dialog title="Filtri" :open.sync="orderStatusFilterDialogState" class="order-lav-filter-dialog" @close="closeDialog()">

        <div class="loader" v-if="!pageLoaded">
            <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
        </div>
        
        <mu-form id="sort-form" ref="form" :model="formSortData" class="filter-form" v-if="pageLoaded">
        
            <div class="form-body">
                <table class="form-table">
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Ordina per</b>
                                <mu-form-item prop="sort-field">
                                    <mu-select v-model="formSortData['sort-field']" color="var(--color-gray2)">
                                        <mu-option label="Data di consegna" value="order-date"></mu-option>
                                        <mu-option label="Data di creazione" value="post_date_gmt"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Ordina</b>
                                <mu-form-item prop="sort-type">
                                    <mu-select v-model="formSortData['sort-type']" color="var(--color-gray2)">
                                        <mu-option label="Crescente" value="ASC"></mu-option>
                                        <mu-option label="Decrescente" value="DESC"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        
        </mu-form>
        
        <hr v-if="pageLoaded" style="border-top: 0px; margin: 15px 4px;">
        
        <mu-form id="filter-form" ref="form" :model="formFilterData" class="filter-form" v-if="pageLoaded">
        
            <div class="form-body">
                <table class="form-table">
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Modello</b>
                                <mu-form-item prop="order-oven_model">
                                    <mu-select v-model="formFilterData['order-oven_model']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_model']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Diametro</b>
                                <mu-form-item prop="order-oven_diameter">
                                    <mu-select v-model="formFilterData['order-oven_diameter']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_diameter']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Finitura</b>
                                <mu-form-item prop="order-oven_topcoat">
                                    <mu-select v-model="formFilterData['order-oven_topcoat']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_topcoat']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Bocca</b>
                                <mu-form-item prop="order-oven_mouth">
                                    <mu-select v-model="formFilterData['order-oven_mouth']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_mouth']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Alimentazione</b>
                                <mu-form-item prop="order-oven_fuel">
                                    <mu-select v-model="formFilterData['order-oven_fuel']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_fuel']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Lato</b>
                                <mu-form-item prop="order-oven_fuel_side">
                                    <mu-select v-model="formFilterData['order-oven_fuel_side']" color="var(--color-gray2)" chips multiple tags>
                                        <mu-option v-for="(label, key, index) in formSelectOptions['order-oven_fuel_side']" :key="index" :label="label" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Davanzalino</b>
                                <mu-form-item prop="order-oven_sill">
                                    <mu-radio v-model="formFilterData['order-oven_sill']" color="var(--color-orange)" label="Tutti" value=""></mu-radio>
                                    <mu-radio v-model="formFilterData['order-oven_sill']" color="var(--color-orange)" label="Si" :value="'true' | Bool"></mu-radio>
                                    <mu-radio v-model="formFilterData['order-oven_sill']" color="var(--color-orange)" label="No" :value="'false' | Bool"></mu-radio>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Etl</b>
                                <mu-form-item prop="order-oven_etl">
                                    <mu-radio v-model="formFilterData['order-oven_etl']" color="var(--color-orange)" label="Tutti" value=""></mu-radio>
                                    <mu-radio v-model="formFilterData['order-oven_etl']" color="var(--color-orange)" label="Si" :value="'true' | Bool"></mu-radio>
                                    <mu-radio v-model="formFilterData['order-oven_etl']" color="var(--color-orange)" label="No" :value="'false' | Bool"></mu-radio>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Periodo</b>
                                <mu-form-item prop="order-date_age">
                                    <mu-select v-model="formFilterData['order-date'].age" color="var(--color-gray2)" @change="changeValue('order-date_age', formFilterData['order-date'].age)">
                                        <mu-option label="Tutto" value=""></mu-option>
                                        <mu-option label="Mese scorso" value="-1"></mu-option>
                                        <mu-option label="Mese corrente" value="0"></mu-option>
                                        <mu-option label="Mese successivo" value="1"></mu-option>
                                        <mu-option label="Da oggi in poi" value="2"></mu-option>
                                        <mu-option label="Personalizzato" value="custom"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="formFilterData['order-date'].age == 'custom'">
                        <td colspan="4">
                            <div class="td-wrap">
                                <b></b>
                                <mu-form-item prop="order-date_start">
                                    <span>DA: </span>
                                    <mu-date-input 
                                        v-model="formFilterData['order-date'].start" 
                                        prop="order-date_start" 
                                        type="date" 
                                        format="DD/MM/YYYY" 
                                        clock-type="24hr" 
                                        :date-time-format="itDateFormat" 
                                        :first-day-of-week="firstDayOfWeek" 
                                        color="var(--color-gray2)"
                                        display-color="var(--color-orange)" 
                                        ></mu-date-input>
                                </mu-form-item>
                                <mu-form-item prop="order-date_end">
                                    <span>A: </span>
                                    <mu-date-input 
                                        v-model="formFilterData['order-date'].end" 
                                        prop="order-date_end" 
                                        type="date" 
                                        format="DD/MM/YYYY" 
                                        clock-type="24hr" 
                                        :date-time-format="itDateFormat" 
                                        :first-day-of-week="firstDayOfWeek" 
                                        color="var(--color-gray2)"
                                        display-color="var(--color-orange)" 
                                        ></mu-date-input>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        
            <mu-form-item class="form-foot">
                <mu-button color="var(--color-orange)" @click="applyFilter()">Applica filtri</mu-button>
                <mu-button color="var(--color-yellow)" @click="applyFilter(true)">Azzera</mu-button>
            </mu-form-item>
        
        </mu-form>
        
        <mu-button slot="actions" flat color="primary" @click="closeDialog()">
            {{ (this.$route.query.source == 'Ordine') ? 'Torna all\'ordine' : 'Chiudi' }}
        </mu-button>

    </mu-dialog>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    
    @import '../assets/css/components/orderStatusFilterDialog.css';

</style>

<script>
    
    /* eslint-disable */
    
    const moment = require('moment');
    
    const dayAbbreviation = ['D', 'L', 'M', 'M', 'G', 'V', 'S'];
    const dayList = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];
    const monthList = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
    const monthLongList = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
    const firstDayOfWeek = 1;

    const itDateFormat = {
        formatDisplay(date) {
            return ""+ dayList[date.getDay()] +", "+ monthList[date.getMonth()] +" "+ date.getDate();
        },
        formatMonth(date) {
            return ""+ monthLongList[date.getMonth()] +" "+ date.getFullYear();
        },
        getWeekDayArray(firstDayOfWeek) {
            let beforeArray = [];
            let afterArray = [];
            for(let i = 0; i < dayAbbreviation.length; i++) {
                if (i < firstDayOfWeek) {
                    afterArray.push(dayAbbreviation[i]);
                } else {
                    beforeArray.push(dayAbbreviation[i]);
                }
            }
            return beforeArray.concat(afterArray);
        },
        getMonthList() {
            return monthList;
        }
    };
    
    export default {
        name: 'OrderStatusFilterDialog',
        props: {
            open: false,
            sortData: {},
            filterData: {}
        },
        data() {
            return {
                moment: moment,
                itDateFormat,
                firstDayOfWeek,
                pageLoaded: false,
                orderStatusFilterDialogState: false,
                formSortData: {
                    'sort-field': 'order-date',
                    'sort-type': 'ASC'
                },
                formFilterData: {
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
                formSelectOptions: {
                    'order-oven_model': {},
                    'order-oven_diameter': {},
                    'order-oven_topcoat': {},
                    'order-oven_mouth': {},
                    'order-oven_fuel': {},
                    'order-oven_fuel_side': {}
                },
            }
        },
        watch: {
            open: function() {
                this.orderStatusFilterDialogState = this.open;
                if(!this.pageLoaded) this.init();
            }
        },
        methods: {
            init: function() {
                
                this.getFormSelectOptions();
            },
            getFormSelectOptions() {
                
                var $this = this;
                
                var fields = {
                    'order-oven_model': 'field_5bf41a9282d08',
                    'order-oven_diameter': 'field_5c5a1b2368233',
                    'order-oven_topcoat': 'field_5bf41bcc82d0b',
                    'order-oven_mouth': 'field_5bf41d6b82d0f',
                    'order-oven_fuel': 'field_5bf41c9b82d0d',
                    'order-oven_fuel_side': 'field_5bf41c1882d0c'
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

                    $this.formSelectOptions = response.body;
                    $this.pageLoaded = true;
                });
            },
            changeValue: function(field, value) {
                if(field == 'order-date_age') {
                    if(value == '-1') {
                        this.formFilterData['order-date'].start = moment().subtract(1, 'months').startOf('month').format('DD/MM/YYYY');
                        this.formFilterData['order-date'].end = moment().subtract(1, 'months').endOf('month').format('DD/MM/YYYY');
                    } else if(value == '0') {
                        this.formFilterData['order-date'].start = moment().startOf('month').format('DD/MM/YYYY');
                        this.formFilterData['order-date'].end = moment().endOf('month').format('DD/MM/YYYY');
                    } else if(value == '1') {
                        this.formFilterData['order-date'].start = moment().add(1, 'months').startOf('month').format('DD/MM/YYYY');
                        this.formFilterData['order-date'].end = moment().add(1, 'months').endOf('month').format('DD/MM/YYYY');
                    } else if(value == '2') {
                        this.formFilterData['order-date'].start = moment().format('DD/MM/YYYY');
                        this.formFilterData['order-date'].end = '';
                    } else if(value == 'custom') {
                        this.formFilterData['order-date'].start = '';
                        this.formFilterData['order-date'].end = '';
                    } else {
                        this.formFilterData['order-date'].start = '';
                        this.formFilterData['order-date'].end = '';
                    }
                }
            },
            applyFilter: function(reset) {
                if(reset) {
                    
                    this.formSortData = {
                        'sort-field': 'order-date',
                        'sort-type': 'ASC'
                    };
                    
                    this.formFilterData = {
                        'order-oven_model': [],
                        'order-oven_diameter': [],
                        'order-oven_topcoat': [],
                        'order-oven_mouth': [],
                        'order-oven_fuel': [],
                        'order-oven_fuel_side': [],
                        'order-oven_sill': '',
                        'order-oven_etl': '',
                        'order-date': {
                            'age': '',
                            'start': '',
                            'end': ''
                        }
                    };
                }
                
                this.$emit('return', {
                    sortData: this.formSortData,
                    filterData: this.formFilterData
                });
            },
            closeDialog: function() {
                
                this.formSortData = Object.assign({}, this.sortData);
                this.formFilterData = Object.assign({}, this.filterData);
                
                this.$emit('return', {
                    sortData: this.formSortData,
                    filterData: this.formFilterData
                });
            }
        },
        filters: {
            Bool(value) {
                return (value == 'true') ? true : false
            }
        },
    }

</script>