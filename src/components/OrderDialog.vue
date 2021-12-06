<!-- eslint-disable vue/no-use-v-if-with-v-for,vue/no-confusing-v-for-v-if -->

<template>
    <mu-dialog :title="modalTitle" :open.sync="orderDialogState" class="order-dialog" :class="'order-dialog-'+ orderAction">

        <div class="loader" v-if="!pageLoaded || !Object.keys(options).length">
            <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
        </div>

        <p v-if="pageLoaded && modalSubTitle" class="mu-dialog-subtitle" :attr-before="order.acf['order-customer'] +' - '">
            <b>Consegna:</b> {{ modalSubTitle }} <br>
            <span class="print-none" v-if="order.acf['order-type'] == 'order'"><b>Stato:</b> {{ (order.acf['order-lav-status'].label) ? order.acf['order-lav-status'].label : 'Nuovo' }}</span>
        </p>

        <div v-if="pageLoaded && Object.keys(options).length" class="order-tools">
            <!--
            <mu-tooltip content="Avviso di presa visione">
                <mu-button v-if="orderAction == 'view'" class="btn-alert" icon :color="(formData['order-alert']) ? 'var(--color-orange)' : 'var(--color-gray2)'" @click="toggleFlag('order-alert')">
                    <mu-icon :value="(formData['order-alert']) ? 'check_circle' : 'panorama_fish_eye'"></mu-icon>
                </mu-button>
            </mu-tooltip>-->

            <mu-button class="btn-print print-none" color="var(--color-orange)" textColor="var(--color-white)" v-if="orderAction == 'view'" @click="print()" small>
                <mu-icon left value="print"></mu-icon>
                STAMPA
            </mu-button>
        </div>

        <mu-form id="order-form" ref="form" :model="formData" class="order-form" :class="'form-'+ orderAction" v-if="pageLoaded && formData['order-type'] && Object.keys(options).length">

            <mu-tabs class="form-head" :value.sync="formData['order-type']" inverse color="primary" indicator-color="primary" text-color="secondary" @change="switchType(formData['order-type'])" v-if="orderAction != 'view'">
                <mu-tab value="order" :disabled="(orderAction == 'view' || isCopy) && formData['order-type'] == 'note'">
                    <mu-icon value="shopping_cart"></mu-icon>Ordine
                </mu-tab>
                <mu-tab value="note" :disabled="(orderAction == 'view' || isCopy) && formData['order-type'] == 'order'">
                    <mu-icon value="notes"></mu-icon>Nota
                </mu-tab>
                <mu-tab value="trip" :disabled="(orderAction == 'view' || isCopy) && formData['order-type'] == 'trip'">
                    <mu-icon value="airplanemode_active"></mu-icon>Trasferta
                </mu-tab>
                <mu-tab value="replacement" :disabled="(orderAction == 'view' || isCopy) && formData['order-type'] == 'replacement'">
                    <mu-icon value="build"></mu-icon>Ricambio
                </mu-tab>
            </mu-tabs>

            <div class="form-body">

                <table class="form-table" v-if="formData['order-type'] == 'order'">
                    <tr v-if="orderAction == 'new'">
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Stato lav. <span>(*)</span></b>
                                <mu-form-item prop="order-lav-status">
                                    <mu-select prop="order-lav-status" v-model="formData['order-lav-status'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-lav-status', formData['order-lav-status'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-lav-status']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Creato da <span>(*)</span></b>
                                <mu-form-item prop="order-user" :rules="formRules['order-user']" v-if="orderAction != 'view'">
                                    <mu-select prop="order-user" v-model="formData['order-user'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-user', formData['order-user'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-user']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-user'].label }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Ordine # <span>(*)</span></b>
                                <mu-form-item prop="order-oven_id" :rules="formRules['order-oven_id']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-oven_id" v-model="formData['order-oven_id']" :disabled="orderAction == 'view' || isCopy" @change="updateOrderId()" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <span v-if="formData['order-index'] > 0 && orderAction != 'view'">-</span>
                                <mu-form-item v-if="formData['order-index'] > 0 && orderAction != 'view'" prop="order-index" :rules="formRules['order-index']" class="order-index-wrap">
                                    <mu-text-field prop="order-index" v-model="formData['order-index']" disabled color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_id'] }} <span v-if="formData['order-index'] > 0"> - {{ formData['order-index'] }}</span></p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>S.N. <span>(*)</span></b>
                                <mu-form-item prop="order-sn" :rules="formRules['order-sn']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-sn" v-model="formData['order-sn']" :disabled="orderAction == 'view' || isCopy" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <span v-if="formData['order-index'] > 0 && orderAction != 'view'">-</span>
                                <mu-form-item v-if="formData['order-index'] > 0 && orderAction != 'view'" prop="order-index" :rules="formRules['order-index']" class="order-index-wrap">
                                    <mu-text-field prop="order-index" v-model="formData['order-index']" disabled color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-sn'] }} <span v-if="formData['order-index'] > 0"> - {{ formData['order-index'] }}</span></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Consegna <span>(*)</span></b>
                                <mu-form-item prop="order-date" :rules="formRules['order-date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-date']" prop="order-date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Modello forno <span>(*)</span></b>
                                <mu-form-item prop="order-oven_model" :rules="formRules['order-oven_model']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_model'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_model', formData['order-oven_model'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_model']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_model'].label }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Diam. <span>(*)</span></b>
                                <mu-form-item prop="order-oven_diameter" :rules="formRules['order-oven_diameter']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_diameter'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_diameter', formData['order-oven_diameter'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_diameter']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_diameter'].label }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Finitura <span>(*)</span></b>
                                <mu-form-item prop="order-oven_topcoat" :rules="formRules['order-oven_topcoat']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_topcoat'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_topcoat', formData['order-oven_topcoat'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_topcoat']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_topcoat'].label }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Bocca <span>(*)</span></b>
                                <mu-form-item prop="order-oven_mouth" :rules="formRules['order-oven_mouth']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_mouth'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_mouth', formData['order-oven_mouth'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_mouth']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_mouth'].label }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Alimentazione <span>(*)</span></b>
                                <mu-form-item prop="order-oven_fuel" :rules="formRules['order-oven_fuel']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_fuel'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_fuel', formData['order-oven_fuel'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_fuel']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_fuel'].label }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Lato <span>(*)</span></b>
                                <mu-form-item prop="order-oven_fuel_side" :rules="formRules['order-oven_fuel_side']" v-if="orderAction != 'view'">
                                    <mu-select v-model="formData['order-oven_fuel_side'].label" :disabled="orderAction == 'view' || formData['order-lav-status'].value == 'lav-status-4' || formData['order-lav-status'].value == 'lav-status-5'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_fuel_side', formData['order-oven_fuel_side'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_fuel_side']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_fuel_side'].label }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Davanzalino</b>
                                <mu-form-item prop="order-oven_sill" :rules="formRules['order-oven_sill']" class="print-none">
                                    <mu-switch v-model="formData['order-oven_sill']" :disabled="orderAction == 'view'" color="var(--color-orange)"></mu-switch>
                                    <span v-if="formData['order-oven_sill']">SI</span>
                                    <span v-else-if="!formData['order-oven_sill']">NO</span>
                                </mu-form-item>
                                <div class="mu-form-item print-block">
                                    <p v-if="formData['order-oven_sill']">SI</p>
                                    <p v-else-if="!formData['order-oven_sill']">NO</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>ETL</b>
                                <mu-form-item prop="order-oven_etl" :rules="formRules['order-oven_etl']" class="print-none">
                                    <mu-switch v-model="formData['order-oven_etl']" :disabled="orderAction == 'view'" color="var(--color-orange)"></mu-switch>
                                    <span v-if="formData['order-oven_etl']">SI</span>
                                    <span v-else-if="!formData['order-oven_etl']">NO</span>
                                </mu-form-item>
                                <div class="mu-form-item print-block">
                                    <p v-if="formData['order-oven_etl']">SI</p>
                                    <p v-else-if="!formData['order-oven_etl']">NO</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="checkProviderTopCoat()">
                        <td colspan="2">
                            <div class="td-wrap sub-td-wrap">
                                <div class="sub-td">
                                    <b>Fornitore</b>
                                    <mu-form-item prop="order-oven_provider" v-if="orderAction != 'view'">
                                        <mu-select v-model="formData['order-oven_provider'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-oven_provider', formData['order-oven_provider'].label)" full-width>
                                            <mu-option v-for="(value, key, index) in formSelectOptions['order-oven_provider']" :key="index" :label="value" :value="value"></mu-option>
                                        </mu-select>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider'].label }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_c1']">
                                    <b>COD 1</b>
                                    <mu-form-item prop="order-oven_provider_c1" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_c1" v-model="formData['order-oven_provider_c1']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_c1'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_q1']">
                                    <b>Q.TA'</b>
                                    <mu-form-item prop="order-oven_provider_q1" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_q1" v-model="formData['order-oven_provider_q1']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_q1'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_c2']">
                                    <b>COD 2</b>
                                    <mu-form-item prop="order-oven_provider_c2" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_c2" v-model="formData['order-oven_provider_c2']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_c2'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_q2']">
                                    <b>Q.TA'</b>
                                    <mu-form-item prop="order-oven_provider_q2" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_q2" v-model="formData['order-oven_provider_q2']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_q2'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_c3']">
                                    <b>COD 3</b>
                                    <mu-form-item prop="order-oven_provider_c3" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_c3" v-model="formData['order-oven_provider_c3']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_c3'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td" v-if="orderAction != 'view' || formData['order-oven_provider_q3']">
                                    <b>Q.TA'</b>
                                    <mu-form-item prop="order-oven_provider_q3" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-oven_provider_q3" v-model="formData['order-oven_provider_q3']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-oven_provider_q3'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="tr-note" v-if="orderAction != 'view' || (orderAction == 'view')">
                        <td colspan="4">
                            <div class="td-wrap td-wrap-mobilefull">
                                <b>Note</b>
                                <div class="mu-form-item" v-if="orderAction == 'view'" v-html="formData['order-note']"></div>
                                <vue-editor :editor-toolbar="initEditor" v-if="orderAction != 'view'" v-model="formData['order-note']" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Preventivo #</b>
                                <mu-form-item prop="order-quote_id" :rules="formRules['order-quote_id']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-quote_id" v-model="formData['order-quote_id']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-quote_id'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Data <span>(*)</span></b>
                                <mu-form-item prop="order-quote_date" :rules="formRules['order-quote_date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-quote_date']" prop="order-quote_date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-quote_date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Cliente <span>(*)</span></b>
                                <mu-form-item prop="order-customer" :rules="formRules['order-customer']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-customer" v-model="formData['order-customer']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-customer'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Destinazione <span>(*)</span></b>
                                <mu-form-item prop="order-destination" :rules="formRules['order-destination']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-destination" v-model="formData['order-destination']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-destination'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="(formData['order-packing'].value == 'packing-3' || formData['order-packing'].value == 'packing-4') ? 2 : 4">
                            <div class="td-wrap">
                                <b>Imballo <span>(*)</span></b>
                                <mu-form-item prop="order-packing" :rules="formRules['order-packing']" v-if="orderAction != 'view'">
                                    <mu-select prop="order-packing" v-model="formData['order-packing'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-packing', formData['order-packing'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-packing']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-packing'].label }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2" v-if="formData['order-packing'].value == 'packing-3' || formData['order-packing'].value == 'packing-4'">
                            <div class="td-wrap">
                                <b>Cassa <span>(*)</span></b>
                                <mu-form-item prop="order-packing_box" :rules="formRules['order-packing_box']" v-if="orderAction != 'view'">
                                    <mu-select prop="order-packing_box" v-model="formData['order-packing_box'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-packing_box', formData['order-packing_box'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-packing_box']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-packing_box'].label }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="(formData['order-transport']) ? 4 : 2">
                            <div class="td-wrap">
                                <b>Trasporto</b>
                                <mu-form-item prop="order-transport" :rules="formRules['order-transport']" class="print-none">
                                    <mu-switch prop="oven-transport" v-model="formData['order-transport']" :disabled="orderAction == 'view'" color="var(--color-orange)"></mu-switch>
                                    <span v-if="formData['order-transport']">A ns. carico</span>
                                    <span v-else-if="!formData['order-transport']">Non a ns. carico</span>
                                </mu-form-item>
                                <div class="mu-form-item print-block">
                                    <p v-if="formData['order-transport']">A ns. carico</p>
                                    <p v-else-if="!formData['order-transport']">Non a ns. carico</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2" v-if="!formData['order-transport']">
                            <div class="td-wrap">
                                <b>Vettore <span>(*)</span></b>
                                <mu-form-item prop="order-forwarder" :rules="formRules['order-forwarder']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-forwarder" v-model="formData['order-forwarder']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-forwarder'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="formData['order-transport']" class="print-none">
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Notti (*)</b>
                                <mu-form-item prop="order-transport_night" :rules="formRules['order-transport_night']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-transport_night" v-model="formData['order-transport_night']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-transport_night'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Tecnici</b>
                                <mu-form-item prop="order-transport_user" v-if="orderAction != 'view'">
                                    <mu-checkbox v-for="(value, key, index) in formSelectOptions['order-transport_user']" :key="index" v-model="formData['order-transport_user']" :value="key" :label="value" :disabled="orderAction == 'view'" color="var(--color-orange)"></mu-checkbox>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>
                                        <span v-if="formData['order-transport_user'].length" v-for="(value, key, index) in formData['order-transport_user']" :key="index">{{ formSelectOptions['order-transport_user'][value] +', ' }}</span>
                                        <span v-if="!formData['order-transport_user'].length">N.D.</span>
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!formData['order-transport']">
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Conf. ritiro</b>
                                <mu-form-item prop="order-delivery_confirmation_date" :rules="formRules['order-delivery_confirmation_date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-delivery_confirmation_date']" prop="order-delivery_confirmation_date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-delivery_confirmation_date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Targhe</b>
                                <mu-form-item prop="order-nameplate" :rules="formRules['order-nameplate']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-nameplate" v-model="formData['order-nameplate']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-nameplate'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="tr-note" v-if="orderAction != 'view' || (orderAction == 'view' && formData['order-attachments_note'])">
                        <td colspan="4">
                            <div class="td-wrap td-wrap-mobilefull">
                                <b>Allegati</b>
                                <div class="mu-form-item" v-if="orderAction == 'view'" v-html="formData['order-attachments_note']"></div>
                                <vue-editor :editor-toolbar="initEditor" v-if="orderAction != 'view'" v-model="formData['order-attachments_note']" />
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="form-table" v-if="formData['order-type'] == 'note'">
                    <tr class="print-none">
                        <td colspan="4">
                            <div class="td-wrap">
                                <mu-form-item prop="order-title" :rules="formRules['order-title']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-title" v-model="formData['order-title']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-title'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Data Ordine <span>(*)</span></b>
                                <mu-form-item prop="order-date" :rules="formRules['order-date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-date']" prop="order-date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="tr-note">
                        <td colspan="4">
                            <div class="td-wrap td-wrap-mobilefull">
                                <b>Note <span>(*)</span></b>
                                <div class="mu-form-item" v-if="orderAction == 'view'" v-html="formData['order-note']"></div>
                                <vue-editor :editor-toolbar="initEditor" v-if="orderAction != 'view'" v-model="formData['order-note']" />
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="form-table" v-if="formData['order-type'] == 'trip'">
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Data <span>(*)</span></b>
                                <mu-form-item prop="order-date" :rules="formRules['order-date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-date']" prop="order-date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Forno <span>(*)</span></b>
                                <mu-form-item prop="tripOrderId" :rules="formRules['trip-order_id']" v-if="orderAction != 'view'">
                                    <mu-select prop="tripOrderId" v-model="tripOrderId" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="selectTripOrder" filterable full-width no-data-text="Nessun risultato">
                                        <mu-option v-for="(value, key, index) in orders" :key="index" :label="(value.acf['order-index'] > 0) ? value.acf['order-oven_id'] +'-'+ value.acf['order-index'] : value.acf['order-oven_id']" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ (order.acf['order-index'] > 0) ? order.acf['order-oven_id'] +'-'+ order.acf['order-index'] : order.acf['order-oven_id'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Luogo</b>
                                <mu-form-item prop="order-destination" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-destination" v-model="formData['order-destination']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-destination'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Notti (*)</b>
                                <mu-form-item prop="order-transport_night" :rules="formRules['order-transport_night']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-transport_night" v-model="formData['order-transport_night']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-transport_night'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Tecnici</b>
                                <mu-form-item prop="order-transport_user" v-if="orderAction != 'view'">
                                    <mu-checkbox v-for="(value, key, index) in formSelectOptions['order-transport_user']" :key="index" v-model="formData['order-transport_user']" :value="key" :label="value" :disabled="orderAction == 'view'" color="var(--color-orange)"></mu-checkbox>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>
                                        <span v-if="formData['order-transport_user'].length" v-for="(value, key, index) in formData['order-transport_user']" :key="index">{{ formSelectOptions['order-transport_user'][value] +', ' }}</span>
                                        <span v-if="!formData['order-transport_user'].length">N.D.</span>
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="tr-note">
                        <td colspan="4">
                            <div class="td-wrap td-wrap-mobilefull">
                                <b>Note</b>
                                <div class="mu-form-item" v-if="orderAction == 'view'" v-html="formData['order-note']"></div>
                                <vue-editor :editor-toolbar="initEditor" v-if="orderAction != 'view'" v-model="formData['order-note']" />
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="form-table" v-if="formData['order-type'] == 'replacement'">
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Creato da <span>(*)</span></b>
                                <mu-form-item prop="order-user" :rules="formRules['order-user']" v-if="orderAction != 'view'">
                                    <mu-select prop="order-user" v-model="formData['order-user'].label" :disabled="orderAction == 'view'" color="var(--color-gray2)" @change="formSelectUpdate('order-user', formData['order-user'].label)" full-width>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-user']" :key="index" :label="value" :value="value"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-user'].label }}</p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Data <span>(*)</span></b>
                                <mu-form-item prop="order-date" :rules="formRules['order-date']" v-if="orderAction != 'view'">
                                    <mu-date-input v-model="formData['order-date']" prop="order-date" type="date" format="DD/MM/YYYY" clock-type="24hr" :date-time-format="itDateFormat" :first-day-of-week="firstDayOfWeek" :disabled="orderAction == 'view'" color="var(--color-gray2)" display-color="var(--color-orange)"></mu-date-input>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-date'] | moment('DD/MM/YYYY') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Ordine # <span>(*)</span></b>
                                <mu-form-item prop="order-oven_id" :rules="formRules['order-oven_id']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-oven_id" v-model="formData['order-oven_id']" :disabled="orderAction == 'view' || isCopy" @change="updateOrderId()" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <span v-if="formData['order-index'] > 0 && orderAction != 'view'">-</span>
                                <mu-form-item v-if="formData['order-index'] > 0 && orderAction != 'view'" prop="order-index" :rules="formRules['order-index']" class="order-index-wrap">
                                    <mu-text-field prop="order-index" v-model="formData['order-index']" disabled color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-oven_id'] }} <span v-if="formData['order-index'] > 0"> - {{ formData['order-index'] }}</span></p>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="td-wrap">
                                <b>Cliente <span>(*)</span></b>
                                <mu-form-item prop="order-customer" :rules="formRules['order-customer']" v-if="orderAction != 'view'">
                                    <mu-text-field prop="order-customer" v-model="formData['order-customer']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-customer'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Parti di ricambio</b>
                                <mu-form-item prop="order-replacement-parts" :rules="formRules['order-replacement-parts']">
                                    <mu-select v-model="formData['order-replacement-parts']" color="var(--color-gray2)" :disabled="orderAction == 'view'" @change="replacementPartsUpdate()" chips multiple tags>
                                        <mu-option v-for="(value, key, index) in formSelectOptions['order-replacement-parts']" :key="index" :label="value" :value="key"></mu-option>
                                    </mu-select>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="inArray(formData['order-replacement-parts'], 'replacement-parts-0')">
                        <td colspan="4">
                            <div class="td-wrap">
                                <b>Parti di ricambio <br> personalizzate </b>
                                <mu-form-item prop="order-replacement-parts_custom" :rules="formRules['order-replacement-parts_custom']">
                                    <mu-text-field
                                            prop="order-lav-parts_custom"
                                            v-model="formData['order-replacement-parts_custom']"
                                            color="var(--color-gray2)"
                                            placeholder="Altro..."
                                    ></mu-text-field>
                                </mu-form-item>
                            </div>
                        </td>
                    </tr>
                    <tr v-for="(value, key) in formData['order-replacement-parts']" :key="key">
                        <td colspan="4">
                            <div class="td-wrap">
                                <b style="white-space: break-spaces;">{{ formSelectOptions['order-replacement-parts'][value] }} </b>
                                <mu-form-item :prop="'order-replacement-'+ value +'-quantity'" v-if="orderAction != 'view'">
                                    <b>Quantità</b>
                                    <mu-text-field :prop="'order-replacement-'+ value +'-quantity'" v-model="formData['order-replacement-parts_attributes'][value]['quantity']" @change="$forceUpdate()"></mu-text-field>
                                </mu-form-item>
                                <mu-form-item :prop="'order-replacement-'+ value +'-unit'" v-if="orderAction != 'view'">
                                    <b>Unità di misura</b>
                                    <mu-text-field :prop="'order-replacement-'+ value +'-unit'" v-model="formData['order-replacement-parts_attributes'][value]['unit']" @change="$forceUpdate()"></mu-text-field>
                                </mu-form-item>
                                <div class="mu-form-item" v-if="orderAction == 'view'">
                                    <p>{{ formData['order-replacement-parts_attributes'][value]['quantity'] +' '+ formData['order-replacement-parts_attributes'][value]['unit'] }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="tr-note">
                        <td colspan="4">
                            <div class="td-wrap td-wrap-mobilefull">
                                <b>Note</b>
                                <div class="mu-form-item" v-if="orderAction == 'view'" v-html="formData['order-note']"></div>
                                <vue-editor :editor-toolbar="initEditor" v-if="orderAction != 'view'" v-model="formData['order-note']" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="td-wrap sub-td-wrap">
                                <div class="sub-td">
                                    <b>Larghezza collo</b>
                                    <mu-form-item prop="order-replacement-size_1" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-replacement-size_1" v-model="formData['order-replacement-size_1']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-replacement-size_1'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td">
                                    <b>Lunghezza collo</b>
                                    <mu-form-item prop="order-replacement-size_2" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-replacement-size_2" v-model="formData['order-replacement-size_2']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-replacement-size_2'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td">
                                    <b>Alteza collo</b>
                                    <mu-form-item prop="order-replacement-size_3" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-replacement-size_3" v-model="formData['order-replacement-size_3']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-replacement-size_3'] }}</p>
                                    </div>
                                </div>
                                <div class="sub-td">
                                    <b>Peso</b>
                                    <mu-form-item prop="order-replacement-weight" v-if="orderAction != 'view'">
                                        <mu-text-field prop="order-replacement-weight" v-model="formData['order-replacement-weight']" :disabled="orderAction == 'view'" color="var(--color-gray2)"></mu-text-field>
                                    </mu-form-item>
                                    <div class="mu-form-item" v-if="orderAction == 'view'">
                                        <p>{{ formData['order-replacement-weight'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="form-gallery print-none" v-if="formData['order-type'] == 'order' || formData['order-type'] == 'replacement'">

                    <mu-expansion-panel class="panel-order_gallery" :expand="(!formData['order-gallery'].length) ? false : true | Bool">

                        <div slot="header">Galleria Ordine</div>

                        <div class="form-uploads">
                            <vue-upload-multiple-image
                                idUpload="image-upload-1"
                                idEdit="image-edit-1"
                                dragText="Trascina qui gli allegati" 
                                browseText="(o) cerca nel dispositivo" 
                                primaryText="Copertina" 
                                markIsPrimaryText="Imposta come copertina" 
                                popupText="Imposta come immagine di copertina" dropText="Lascia il tuo file qui ..." 
                                accept="*"
                                :maxImage="15"
                                @upload-success="uploadImageSuccess" 
                                @before-remove="beforeRemove" 
                                @edit-image="editImage" 
                                @mark-is-primary="markAsPrimary" 
                                :data-images="formData['order-gallery']" 
                                :disabled="(orderAction == 'view') ? true : false | Bool"></vue-upload-multiple-image>
                        </div>

                    </mu-expansion-panel>

                    <mu-expansion-panel class="panel-order_lav_gallery" :expand="(!formData['order-lav-gallery'].length) ? false : true | Bool" v-if="formData['order-type'] == 'order'">

                        <div slot="header">Galleria Lavorazione</div>

                        <div class="form-uploads">
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
                                :data-images="formData['order-lav-gallery']" 
                                disabled></vue-upload-multiple-image>
                        </div>
                    </mu-expansion-panel>
                </div>

            </div>

            <mu-form-item class="form-foot">
                <mu-button color="var(--color-orange)" v-if="orderAction == 'view' && !this.$route.query.source && order.acf['order-type'] == 'order'" @click="viewStatus(order.id)">STATO LAVORAZIONE</mu-button>
                <mu-button color="var(--color-yellow)" v-if="orderAction == 'view' && !this.$route.query.source && order.acf['order-type'] == 'order'" @click="viewHistory(order.id)">STORIA</mu-button>
                <mu-button color="orange" @click="saveOrder()" v-if="orderAction != 'view'">Salva</mu-button>
                <mu-button color="orange" @click="saveOrder(true)" v-if="orderAction != 'view' && !isCopy">Salva & Copia</mu-button>
                <mu-button flat color="red" @click="undoOrder" v-if="orderAction != 'view'">Annulla</mu-button>
                <mu-button color="orange" @click="editOrder()" v-if="orderAction == 'view' && user.role == 'admin'">Modifica</mu-button>
                <mu-button color="red" @click="deleteOrder()" :disabled="orderAction == 'new'" v-if="orderAction == 'view' && user.role == 'admin'">Elimina</mu-button>
            </mu-form-item>

        </mu-form>

        <mu-button slot="actions" flat color="primary" @click="closeOrderDialog()" v-if="orderAction == 'view'">
            {{ (this.$route.query.source == 'Stato Ordine') ? 'Torna allo stato di lavorazione' :  'Chiudi' }}
        </mu-button>

    </mu-dialog>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    @import '../assets/css/components/orderDialog.css';

    @import "~vue2-editor/dist/vue2-editor.css";

</style>

<script>

    /* eslint-disable */

    import Editor from '@tinymce/tinymce-vue';
    import {
        VueEditor
    } from "vue2-editor";
    import VueUploadMultipleImage from 'vue-upload-multiple-image';

    const moment = require('moment');

    const dayAbbreviation = ['D', 'L', 'M', 'M', 'G', 'V', 'S'];
    const dayList = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];
    const monthList = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
    const monthLongList = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
    const firstDayOfWeek = 1;

    const itDateFormat = {
        formatDisplay(date) {
            return "" + dayList[date.getDay()] + ", " + monthList[date.getMonth()] + " " + date.getDate();
        },
        formatMonth(date) {
            return "" + monthLongList[date.getMonth()] + " " + date.getFullYear();
        },
        getWeekDayArray(firstDayOfWeek) {
            let beforeArray = [];
            let afterArray = [];
            for (let i = 0; i < dayAbbreviation.length; i++) {
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
        name: 'OrderDialog',
        props: {
            action: false,
            orderData: false,
            user: {
                username: 'guest',
                role: 'guest'
            }
        },
        data() {
            return {
                moment: moment,
                modalTitle: '',
                modalSubTitle: '',
                pageLoaded: false,
                itDateFormat,
                firstDayOfWeek,
                /*
                initEditor: {
                    plugins: 'wordcount',
                    toolbar: 'undo redo',
                    menubar: false,
                    statusbar: false
                },
                */
                initEditor: [
                    []
                ],
                orderAction: this.action,
                orderDialogState: false,
                options: {},
                order: {},
                orders: [],
                tripOrderId: '',
                formData: {
                    'order-title': '',
                    'order-type': 'order',
                    'order-quote_date': moment().format(),
                    'order-quote_id': '',
                    'order-oven_id': '',
                    'order-index': 0,
                    'order-date': '',
                    'order-oven_model': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-oven_diameter': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-customer': '',
                    'order-destination': '',
                    'order-oven_topcoat': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-oven_fuel': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-oven_fuel_side': {
                        value: 'oven_fuel_side-1',
                        label: 'Sinistro'
                    },
                    'order-oven_sill': false,
                    'order-oven_etl': false,
                    'order-oven_provider': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-oven_provider_c1': '',
                    'order-oven_provider_q1': '',
                    'order-oven_provider_c2': '',
                    'order-oven_provider_q2': '',
                    'order-oven_provider_c3': '',
                    'order-oven_provider_q3': '',
                    'order-oven_mouth': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-replacement-parts': [],
                    'order-replacement-parts_custom': '',
                    'order-replacement-parts_attributes': {},
                    'order-replacement-size_1': '',
                    'order-replacement-size_2': '',
                    'order-replacement-size_3': '',
                    'order-replacement-weight': '',
                    'order-note': '',
                    'order-attachments_note': '',
                    'order-packing': {
                        value: 'packing-1',
                        label: 'Montato'
                    },
                    'order-packing_box': {
                        value: 'packing_box-0',
                        label: 'Altro'
                    },
                    'order-transport': false,
                    'order-transport_night': 0,
                    'order-transport_user': [],
                    'order-forwarder': '',
                    'order-gallery': [],
                    'order-sn': '',
                    'order-delivery_confirmation_date': '',
                    'order-nameplate': '',
                    'order-user': {
                        value: 'false',
                        label: 'N.D.'
                    },
                    'order-lav-status': {
                        value: 'lav-status-0',
                        label: 'Nuovo'
                    },
                    'order-alert': false,
                    'order-lav-edit_note': false,
                    'order-lav-edit_oven_props': false,
                    'order-lav-edit_transport': false,
                    'order-lav-edit_date': false,
                    'order-lav-edit_pickup': false,
                    'order-history': [],
                    'order-lav-gallery': []
                },
                formRules: {
                    'order-title': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-quote_date': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-date': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-customer': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-oven_id': [{
                            validate: (val) => !!val,
                            message: 'Campo obbligatorio'
                        },
                        {
                            validate: (val) => val.length >= 3,
                            message: 'Inserire almeno 3 caratteri'
                        }
                    ],
                    'trip-oven_id': [{
                            validate: (val) => !!val,
                            message: 'Campo obbligatorio'
                        },
                        {
                            validate: (val) => val.length >= 3,
                            message: 'Inserire almeno 3 caratteri'
                        }
                    ],
                    'replacement-oven_id': [{
                            validate: (val) => !!val,
                            message: 'Campo obbligatorio'
                        },
                        {
                            validate: (val) => val.length >= 3,
                            message: 'Inserire almeno 3 caratteri'
                        }
                    ],
                    'order-oven_model': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-oven_diameter': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-oven_mouth': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-oven_topcoat': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-oven_fuel': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-oven_fuel_side': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-packing': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-packing_box': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-user': [{
                        validate: (val) => val.label != 'N.D.',
                        message: 'Selezionare un\'opzione'
                    }],
                    'order-forwarder': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-transport_night': [{
                        validate: (val) => (val == '' || (val != '' && Number.isInteger(parseInt(val)))),
                        message: 'Inserire un numero'
                    }],
                    'order-destination': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-sn': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }],
                    'order-replacement-parts_custom': [{
                        validate: (val) => !!val,
                        message: ''
                    }],
                    'order-replacement-parts_attributes-quantity': [{
                        validate: (val) => (val == '' || (val != '' && Number.isInteger(parseInt(val)))),
                        message: 'Inserire un numero'
                    }],
                    'order-replacement-parts_attributes-unit': [{
                        validate: (val) => !!val,
                        message: 'Campo obbligatorio'
                    }]
                },
                formSelectOptions: {
                    'order-user': {},
                    'order-oven_model': {},
                    'order-oven_diameter': {},
                    'order-oven_topcoat': {},
                    'order-oven_mouth': {},
                    'order-oven_fuel': {},
                    'order-oven_fuel_side': {},
                    'order-packing': {},
                    'order-packing_box': {},
                    'order-lav-status': {},
                    'order-oven_provider': {},
                    'order-transport_user': {},
                    'order-replacement-parts': []
                },
                isCopy: false
            }
        },
        watch: {
            $route: function() {
                if (this.$route.name == "Ordine" && this.checkUser()) {

                    this.pageLoaded = false;
                    this.orderAction = this.action;
                    this.openOrderDialog();
                }
            },
            orderDialogState: function() {
                if (!this.orderDialogState) this.$router.push({
                    name: 'Calendario Ordini'
                });
            }
        },
        created() {

            if (this.$route.name == "Ordine" && this.checkUser()) this.openOrderDialog();
        },
        methods: {
            checkUser: function() {
                if (this.action != 'view' && this.user.role != 'admin') {
                    this.orderDialogState = false;
                    this.$router.push({
                        name: 'Calendario Ordini'
                    });
                    return false;
                }
                return true;
            },
            openOrderDialog: function() {

                this.resetData();
                this.pageLoaded = false;
                this.orderDialogState = true;

                if (this.orderAction == 'new') {

                    this.addOrder(this.orderData);

                } else {

                    this.getOrderData();
                }

                this.getOptions();
                this.getFormSelectOptions();
            },
            closeOrderDialog: function() {
                this.orderDialogState = false;
                if (!this.$route.query.source) {
                    this.$router.push({
                        name: 'Calendario Ordini'
                    });
                } else {
                    this.$router.push({
                        name: this.$route.query.source,
                        params: {
                            orderData: this.order.id
                        }
                    });
                }
            },
            switchType: function(orderType) {

                this.formData.orderType = orderType;

                if (orderType == 'trip' || orderType == 'replacement') this.getOrders();

            },
            updateOrderId: function() {

                var activeDay = (this.orderAction == 'edit') ? moment(this.formData['order-date']) : moment(this.orderData);

                if (this.formData['order-type'] != 'replacement') {
                    this.formData['order-oven_id'] = this.formData['order-oven_id'].replace(/[^0-9]/g, '');
                    this.formData['order-sn'] = (this.formData['order-oven_id']) ? activeDay.format('YYMM') + '' + this.formData['order-oven_id'] : '';
                } else {
                    this.formData['order-oven_id'] = this.formData['order-oven_id'];
                }

                if (this.orderAction == 'edit') {
                    this.formData['order-index'] = (this.formData['order-oven_id'] != this.order.acf['order-oven_id']) ? 0 : this.order.acf['order-index'];
                }

                if (this.formData['order-type'] == 'order') {
                    this.formData['order-title'] = 'Ordine #' + this.formData['order-oven_id'];
                } else if (this.formData['order-type'] == 'trip') {
                    this.formData['order-title'] = 'Trasferta #' + this.formData['order-oven_id'];
                } else if (this.formData['order-type'] == 'replacement') {
                    this.formData['order-title'] = 'Ricambio #' + this.formData['order-oven_id'];
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

                });
            },
            getOrders: function() {

                var $this = this;

                var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    params = {
                        post_type: 'ordini',
                        posts_per_page: -1,
                        orderby: 'date',
                        order: 'DESC',
                        meta_query: {
                            1: {
                                key: 'order-type',
                                value: 'order',
                                compare: '='
                            }
                        }
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        meta: [
                            'order-oven_id',
                            'order-index'
                        ]
                    };

                this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                    data: {
                        'parametri': params,
                        'opzioni': options,
                        'chiave' : 'ordini_calendario'
                    }
                }).then(function(response) {

                    $this.orders = [];
                    $this.orders = response.body;

                });
            },
            getOrderData: function() {

                var $this = this;

                this.$http.get(this.$app.api.host + '/wp-json/wp/v2/ordini/' + this.orderData, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(function(response) {

                    $this.pageLoaded = true;
                    
                    response.body.slug = response.body.slug.replace('order-', '');
                    Object.assign($this.order, response.body);

                    $this.initOrder($this);

                    $this.$forceUpdate();
                });
            },
            getFormSelectOptions() {

                var $this = this;

                var fields = {
                    'order-user': 'field_5c18d0d749f1a',
                    'order-oven_model': 'field_5bf41a9282d08',
                    'order-oven_diameter': 'field_5c5a1b2368233',
                    'order-oven_topcoat': 'field_5bf41bcc82d0b',
                    'order-oven_mouth': 'field_5bf41d6b82d0f',
                    'order-oven_fuel': 'field_5bf41c9b82d0d',
                    'order-oven_fuel_side': 'field_5bf41c1882d0c',
                    'order-packing': 'field_5bf41ebc82d10',
                    'order-packing_box': 'field_5c6401f14dca1',
                    'order-lav-status': 'field_5c828f92a2e4e',
                    'order-oven_provider': 'field_5cc96895736f3',
                    'order-transport_user': 'field_5ccaeefba6ca4',
                    'order-replacement-parts': 'field_5e5fb8fd914a9'
                };

                this.$http.get(this.$app.api.host + '/wp-json/wp/v2/ordini/options', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    params: {
                        field: fields
                    }
                }).then(function(response) {
                    // console.log(response);
                    $this.formSelectOptions = JSON.parse(response.bodyText);
                });
            },
            formSelectUpdate(field, label) {

                for (var prop in this.formSelectOptions[field]) {
                    if (this.formSelectOptions[field][prop] == label) {
                        this.formData[field].value = prop;
                    }
                }
            },
            replacementPartsUpdate: function() {

                Object.keys(this.formData['order-replacement-parts_attributes']).forEach((part) => {
                    if (!this.formData['order-replacement-parts'][part]) {
                        delete this.formData['order-replacement-parts_attributes'][part];
                    }
                });

                this.formData['order-replacement-parts'].forEach((part) => {
                    console.log(this.formData['order-replacement-parts_attributes']);
                    if (!this.formData['order-replacement-parts_attributes'].hasOwnProperty(part)) {
                        this.formData['order-replacement-parts_attributes'][part] = {
                            item: this.formSelectOptions['order-replacement-parts'][part],
                            quantity: 0,
                            unit: ''
                        }
                    }
                });
            },
            checkProviderTopCoat() {

                var $this = this,
                    $return = false;

                this.options['options-oven_provider'].forEach(function(item) {
                    if (item['order-oven_topcoat'].value == $this.formData['order-oven_topcoat'].value) {
                        $return = true;
                    }
                });

                return $return;
            },
            selectTripOrder: function(order) {
                
                var $this = this,
                    headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    params = {
                        post_type: 'ordini',
                        posts_per_page: -1,
                        orderby: 'date',
                        order: 'DESC',
                        p: order.ID
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        meta: [
                            'order-oven_id',
                            'order-index',
                            'order-date',
                            'order-destination',
                            'order-transport',
                            'order-transport_user',
                            'order-transport_night'
                        ]
                    };

                this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                    data: {
                        'parametri': params,
                        'opzioni': options,
                        'chiave' : 'ordini_calendario'
                    }
                }).then(function(response) {

                    var $this = this, i = 0;
                    
                    for (var key in $this.formData) {

                        var currValue = (response.body[0].acf && response.body[0].acf[key]) ? response.body[0].acf[key] : $this.formData[key];

                        if (key != 'order-type' && key != 'order-date' && key != 'order-note') {

                            if (key == 'order-oven_id') {

                                $this.formData[key] = currValue.split('-')[0];
                                $this.formData[key] = currValue;

                            } else if (key == 'order-index') {

                                $this.formData[key] = parseInt(currValue);

                            } else if (key == 'order-transport_user') {

                                var i = 0;
                                
                                $this.formData[key] = [];
                                if(response.body[0].acf['order-transport']) {
                                    for (var user in currValue) {
                                        $this.formData[key][i] = currValue[user].value;
                                        i = i + 1;
                                    }
                                }

                            } else {

                                $this.formData[key] = currValue;
                            }
                        }
                    }
                });
            },
            initOrder: function($this = this) {

                var order_data = JSON.parse(JSON.stringify($this.order));

                // INTI MODAL DATA
                for (var key in $this.formData) {

                    var currValue = (order_data.acf && order_data.acf[key]) ? order_data.acf[key] : $this.formData[key];

                    if (typeof $this.formData[key] === 'boolean') {

                        $this.formData[key] = (currValue) ? true : false;

                    } else {

                        if (key == 'order-gallery' || key == 'order-lav-gallery') {

                            if(typeof currValue == 'string') {
                                $this.formData[key] = JSON.parse(currValue);
                            } else if(Array.isArray(currValue)) {
                                $this.formData[key] = currValue;
                            } else {
                                $this.formData[key] = [];
                            }

                            if(!Array.isArray($this.formData[key])) {
                                $this.formData[key] = Object.keys($this.formData[key]).map(function(_key) {
                                    return $this.formData[key][_key];
                                });
                            }
                            
                        } else if (key == 'order-title') {

                            $this.formData[key] = order_data.title.rendered;

                            if ($this.orderAction == 'view') {

                                this.modalTitle = (order_data.acf['order-index'] != 0 && order_data.acf['order-type'] == 'order') ? $this.formData[key] + '-' + order_data.acf['order-index'] : $this.formData[key];

                                this.modalTitle = $this.modalTitle;
                                this.modalSubTitle = moment(order_data.acf['order-date'], 'YYYY-MM-DD').format('DD/MM/YYYY');

                            } else {
                                this.modalTitle = 'Modifica Ordine/Nota';
                            }

                        } else if (key == 'order-index') {

                            $this.formData[key] = parseInt(currValue);

                        } else if (key == 'order-oven_id') {

                            $this.formData[key] = currValue.split('-')[0];

                        } else if(key == 'order-date') {

                            $this.formData[key] = moment(currValue, 'YYYY-MM-DD');

                        } else if(key == 'order-replacement-parts') {

                            var parts = [], i = 0;
                            for(var part in $this.order.acf[key]) {
                                parts[i] = $this.order.acf[key][part]['value'];
                                i = i + 1;
                            }

                            $this.formData[key] = parts;

                        } else if(key == 'order-replacement-parts_attributes') {

                            if($this.order.acf.hasOwnProperty('order-replacement-parts_attributes')){
                                $this.formData[key] = (JSON.parse($this.order.acf[key])) ? JSON.parse($this.order.acf[key]) : {};
                            }

                        } else {

                            if (typeof currValue === 'object' && key != 'order-history') {

                                if (currValue.length > 0) {
                                    var _currValue = [];
                                    for (var value in currValue) {
                                        _currValue[value] = currValue[value].value;
                                    }
                                    currValue = _currValue;
                                }

                                Object.assign($this.formData[key], currValue);

                            } else {

                                $this.formData[key] = currValue;
                            }
                        }
                    }
                }

                if ($this.formData['order-type'] == 'note') {

                    $this.formData['order-title'] = order_data.title.rendered;
                    $this.initEditor['initial-value'] = $this.formData['order-note'];
                }

                if ($this.formData['order-type'] == 'trip') {

                    $this.getOrders();

                    $this.formData['order-title'] = order_data.title.rendered;
                    $this.tripOrderId = ($this.formData['order-index']) ? $this.formData['order-oven_id'] + '-' + $this.formData['order-index'] : $this.formData['order-oven_id'];
                    $this.initEditor['initial-value'] = $this.formData['order-note'];
                }

                if ($this.formData['order-type'] == 'replacement') {

                    $this.formData['order-title'] = order_data.title.rendered;
                    $this.initEditor['initial-value'] = $this.formData['order-note'];
                }
            },
            editOrder: function() {

                this.modalTitle = 'Modifica Ordine/Nota';

                this.orderAction = 'edit';
                this.$router.push({
                    name: 'Ordine',
                    params: {
                        action: 'edit',
                        orderData: this.orderData
                    }
                });
            },
            undoOrder: function() {

                if (this.orderAction == 'edit') {

                    this.orderAction = 'view';
                    this.$router.push({
                        name: 'Ordine',
                        params: {
                            action: 'view',
                            orderData: this.orderData
                        }
                    });

                    this.initOrder(this);

                } else if (this.orderAction == 'new') {

                    this.closeOrderDialog();
                }
            },
            addOrder: function(orderDate) {

                this.modalTitle = 'Aggiungi Nuovo Ordine/Nota';

                if (this.$route.query.params) {

                    this.isCopy = true;

                    var data = JSON.parse(this.$route.query.params);

                    this.formData = data.meta;
                    this.formData['order-title'] = data['order-title'];

                } else {

                    this.formData['order-title'] = '';
                    this.formData['order-date'] = orderDate;
                }
                
                this.pageLoaded = true;
            },
            deleteOrder: function() {

                var $this = this;

                var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        // 'Authorization': 'Basic ' + this.$app.api.token
                        'Authorization': 'Basic '
                    },
                    params = {
                        id: this.orderData,
                        force: true
                    };

                if (this.formData['order-gallery'].length) {

                    for (var index = 0; index < this.formData['order-gallery'].length; index = index + 1) {

                        if (index == (this.formData['order-gallery'].length - 1)) {

                            this.deleteGalleryImages(index).then(function(response) {

                                this.$http.delete(this.$app.api.host + '/wp-json/wp/v2/ordini/delete', {
                                    headers: headers,
                                    params: params
                                }).then(function(response) {

                                    if ($this.order.acf['order-type'] == 'order') {

                                        var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                                        $this.options['options-user-email'].forEach(function(item, index) {
                                            if (item['order-user'].value == $this.order.acf['order-user'].value && $this.options['options-user-email_status'].indexOf('lav-status-delete') > -1) {
                                                $this.sendMail(
                                                    $this,
                                                    'noreply@mamforni.it',
                                                    item['user-email'],
                                                    'MAM Forni - Calendario Ordini | Eliminazione Ordine #' + order_id,
                                                    'L\'ordine #' + order_id + ' è stato eliminato!'
                                                );
                                            }
                                        });

                                    } else if($this.order.acf['order-type'] == 'replacement') {

                                        var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                                        $this.options['options-user-email'].forEach(function(item, index) {
                                            if (item['order-user'].value == $this.order.acf['order-user'].value && $this.options['options-user-email_status'].indexOf('lav-status-delete') > -1) {
                                                $this.sendMail(
                                                    $this,
                                                    'noreply@mamforni.it',
                                                    item['user-email'],
                                                    'MAM Forni - Calendario Ordini | Eliminazione Ordine #' + order_id,
                                                    'L\'ordine #' + order_id + ' è stato eliminato!'
                                                );
                                            }
                                        });
                                    }

                                    $this.$emit('return', {
                                        reloadOrder: true,
                                        message: 'Ordine eliminato con successo!',
                                        status: response.ok
                                    });

                                    this.closeOrderDialog();

                                }, function(error) {

                                    $this.$emit('return', {
                                        reloadOrder: false,
                                        message: 'Impossibile eliminare l\'ordine. (#' + error.status + ')',
                                        status: error.ok
                                    });
                                });

                            });

                        } else {

                            this.deleteGalleryImages(index);
                        }
                    }

                } else {

                    this.$http.delete(this.$app.api.host + '/wp-json/wp/v2/ordini/delete', {
                        headers: headers,
                        params: params
                    }).then(function(response) {

                        $this.$emit('return', {
                            reloadOrder: true,
                            message: 'Ordine eliminato con successo!',
                            status: response.ok
                        });

                        this.closeOrderDialog();

                    }, function(error) {
                        console.log(error);

                        $this.$emit('return', {
                            reloadOrder: false,
                            message: 'Impossibile eliminare l\'ordine2. (#' + error.status + ')',
                            status: error.ok
                        });
                    });
                }

            },
            saveOrder: function(copy = false) {

                var $this = this;

                this.$refs.form.validate().then((result) => {

                    if (result) {

                        $this.pageLoaded = false;

                        if ($this.formData['order-type'] == 'trip') {
                            $this.formData['order-title'] = ($this.formData['order-index']) ? 'Trasferta #' + $this.formData['order-oven_id'] + '-' + $this.formData['order-index'] : 'Trasferta #' + $this.formData['order-oven_id'];
                        }

                        if ($this.formData['order-type'] == 'replacement') {
                            $this.formData['order-title'] = ($this.formData['order-index']) ? 'Ricambio #' + $this.formData['order-oven_id'] + '-' + $this.formData['order-index'] : 'Ricambio #' + $this.formData['order-oven_id'];
                        }

                        for (var key in $this.formData) {

                            if (key.indexOf('date') > -1) {
                                $this.formData[key] = ($this.formData[key]) ? moment($this.formData[key]).format('YYYY-MM-DD') : $this.formData[key];
                            } else if (key == 'order-transport_user') {
                                $this.formData[key] = ($this.formData[key].length) ? $this.formData[key] : false;
                            } else if (key == 'order-note' || key == 'order-attachments_note') {
                                $this.formData[key] = $this.formData[key].replace(/"/g, "'").replace(/\\/g, "/");
                                $this.formData[key] = $this.htmlEntities('decode', $this.formData[key]);
                            } else if (key == 'order-gallery') {
                                if (typeof $this.formData[key] == 'string') {
                                    $this.formData[key] = $this.formData[key].replace(/"/g, "").replace(/\\/g, "").replace(/'/g, "").replace(/\//g, "");
                                }
                            }
                        }

                        var options = {
                                emulateJSON: true
                            },
                            params = {
                                id: ($this.orderAction == 'edit') ? $this.orderData : 0,
                                'order-title': $this.formData['order-title'],
                                meta: {
                                    'order-type': $this.formData['order-type'],
                                    'order-user': $this.formData['order-user'],
                                    'order-alert': $this.formData['order-alert'],
                                    'order-oven_id': $this.formData['order-oven_id'],
                                    'order-sn': $this.formData['order-sn'],
                                    'order-index': $this.formData['order-index'],
                                    'order-quote_id': $this.formData['order-quote_id'],
                                    'order-quote_date': $this.formData['order-quote_date'],
                                    'order-delivery_confirmation_date': $this.formData['order-delivery_confirmation_date'],
                                    'order-nameplate': $this.formData['order-nameplate'],
                                    'order-customer': $this.formData['order-customer'],
                                    'order-destination': $this.formData['order-destination'],
                                    'order-date': $this.formData['order-date'],
                                    'order-oven_model': $this.formData['order-oven_model'],
                                    'order-oven_diameter': $this.formData['order-oven_diameter'],
                                    'order-oven_topcoat': $this.formData['order-oven_topcoat'],
                                    'order-oven_mouth': $this.formData['order-oven_mouth'],
                                    'order-oven_fuel': $this.formData['order-oven_fuel'],
                                    'order-oven_fuel_side': $this.formData['order-oven_fuel_side'],
                                    'order-oven_sill': $this.formData['order-oven_sill'],
                                    'order-oven_etl': $this.formData['order-oven_etl'],
                                    'order-note': $this.formData['order-note'],
                                    'order-packing': $this.formData['order-packing'],
                                    'order-packing_box': $this.formData['order-packing_box'],
                                    'order-transport': $this.formData['order-transport'],
                                    'order-transport_night': $this.formData['order-transport_night'],
                                    'order-transport_user': $this.formData['order-transport_user'],
                                    'order-forwarder': $this.formData['order-forwarder'],
                                    'order-oven_provider': $this.formData['order-oven_provider'],
                                    'order-oven_provider_c1': $this.formData['order-oven_provider_c1'],
                                    'order-oven_provider_q1': $this.formData['order-oven_provider_q1'],
                                    'order-oven_provider_c2': $this.formData['order-oven_provider_c2'],
                                    'order-oven_provider_q2': $this.formData['order-oven_provider_q2'],
                                    'order-oven_provider_c3': $this.formData['order-oven_provider_c3'],
                                    'order-oven_provider_q3': $this.formData['order-oven_provider_q3'],
                                    'order-replacement-parts': (typeof $this.formData['order-replacement-parts'] !== 'undefined' && $this.formData['order-replacement-parts'].length) ? $this.formData['order-replacement-parts'] : false,
                                    'order-replacement-parts_custom': (typeof $this.formData['order-replacement-parts_custom'] !== 'undefined' && $this.formData['order-replacement-parts_custom'].length) ? $this.formData['order-replacement-parts_custom'] : false,
                                    'order-replacement-parts_attributes': $this.formData['order-replacement-parts_attributes'],
                                    'order-replacement-size_1': $this.formData['order-replacement-size_1'],
                                    'order-replacement-size_2': $this.formData['order-replacement-size_2'],
                                    'order-replacement-size_3': $this.formData['order-replacement-size_3'],
                                    'order-replacement-weight': $this.formData['order-replacement-weight'],
                                    'order-attachments_note': $this.formData['order-attachments_note'],
                                    'order-gallery': $this.formData['order-gallery']
                                }
                            };

                        if ($this.orderAction == 'new' && $this.formData['order-type'] == 'order') {
                            params.meta['order-lav-status'] = $this.formData['order-lav-status'];
                            params.meta['order-lav-edit_date'] = false;
                            params.meta['order-lav-edit_oven_props'] = false;
                            params.meta['order-lav-edit_note'] = false;
                            params.meta['order-lav-user'] = {
                                value: 'lav-user-0',
                                label: 'Altro'
                            };
                            params.meta['order-lav-parts'] = {};
                        }

                        this.$http.post(this.$app.api.host + '/wp-json/wp/v2/ordini/update', params, options).then(function(response) {

                            if ($this.orderAction == 'edit') {

                                if ($this.formData['order-type'] == 'order') {

                                    var updated_meta = false,
                                        order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                                    var editProps = [];

                                    for (var key in response.body.meta) {

                                        var _old = '',
                                            _old_value = '',
                                            _new = '',
                                            _new_value = '';

                                        if (key.indexOf('date') >= 0) {

                                            if ($this.order.acf[key]) {
                                                $this.order.acf[key] = $this.order.acf[key].split('+');
                                                $this.order.acf[key] = $this.order.acf[key][0];
                                                _old_value = _old = moment($this.order.acf[key]).format('DD-MM-YYYY');
                                            }

                                            if (response.body.meta[key]) {
                                                response.body.meta[key] = response.body.meta[key].split('+');
                                                response.body.meta[key] = response.body.meta[key][0];
                                                _new_value = _new = moment(response.body.meta[key]).format('DD-MM-YYYY');
                                            }

                                        } else if (key == 'order-transport_user') {

                                            _old = (!$this.order.acf[key]) ? false : $this.order.acf[key].length;
                                            _old_value = (!$this.order.acf[key]) ? false : $this.order.acf[key];

                                            if (!response.body.meta[key] || response.body.meta[key] == 'false' || !response.body.meta[key].length) {

                                                _new_value = _new = false;

                                            } else {

                                                _new_value = [];
                                                for (var subkey in response.body.meta[key]) {
                                                    _new_value.push({
                                                        value: response.body.meta[key][subkey],
                                                        label: $this.formSelectOptions['order-transport_user'][response.body.meta[key][subkey]]
                                                    });
                                                }
                                                _new = _new_value.length;
                                            }

                                        } else if (key == 'order-note' || key == 'order-attachments_note') {

                                            _old_value = _old = $this.order.acf[key].replace(/"/g, "'");
                                            _new_value = _new = response.body.meta[key].replace(/\\"/g, "'").replace(/\\'/g, "'");

                                        } else if (key == 'order-gallery') {

                                            /*
                                            _old_value = JSON.parse($this.order.acf[key]);
                                            _new_value = JSON.parse(response.body.meta[key]);
                                            
                                            if(_old_value.length) {
                                                for(var img in _old_value) {
                                                    _old = _old +','+ img.name;
                                                }
                                            }
                                            
                                            if(_new_value.length) {
                                                for(var img in _new_value) {
                                                    _new = _new +','+ img.name;
                                                }
                                            }
                                            */

                                        } else if (key != 'order-history' && key.indexOf('order-lav-edit_') == -1 && key != 'order-alert') {

                                            if (typeof $this.order.acf[key] === 'object') {

                                                _old = $this.order.acf[key].value;
                                                _old_value = $this.order.acf[key];

                                                _new = response.body.meta[key].value;
                                                _new_value = response.body.meta[key];

                                            } else if (typeof $this.order.acf[key] === 'array') {

                                            } else if (typeof $this.order.acf[key] === 'boolean' || $this.order.acf[key] == 'false') {

                                                _old_value = _old = $this.order.acf[key];
                                                _new_value = _new = (response.body.meta[key] == true || response.body.meta[key] == 'true') ? true : false;

                                            } else {

                                                _old_value = _old = $this.order.acf[key];
                                                _new_value = _new = response.body.meta[key];
                                            }
                                        }

                                        if (_old != _new) {

                                            if (key == 'order-note') {

                                                if ($this.formData['order-lav-status'].value != 'lav-status-0') {
                                                    $this.formData['order-lav-edit_note'] = false;
                                                    $this.toggleFlag('order-lav-edit_note');
                                                }
                                                editProps.push(3);

                                            }
                                            if (key == 'order-destination' || key == 'order-transport' || key == 'order-forwarder') {

                                                if ($this.formData['order-lav-status'].value != 'lav-status-0') {
                                                    $this.formData['order-lav-edit_transport'] = false;
                                                    $this.toggleFlag('order-lav-edit_transport');
                                                }
                                                editProps.push(4);

                                            }
                                            if (key == 'order-date') {

                                                if ($this.formData['order-lav-status'].value != 'lav-status-0') {
                                                    $this.formData['order-lav-edit_date'] = false;
                                                    $this.toggleFlag('order-lav-edit_date');
                                                }
                                                editProps.push(1);

                                            }
                                            if (key == 'order-oven_model' || key == 'order-oven_diameter' || key == 'order-oven_topcoat' || key == 'order-oven_mouth' || key == 'order-oven_fuel' || key == 'order-oven_fuel_side') {

                                                if ($this.formData['order-lav-status'].value != 'lav-status-0') {
                                                    $this.formData['order-lav-edit_oven_props'] = false;
                                                    $this.toggleFlag('order-lav-edit_oven_props');
                                                }
                                                editProps.push(2);

                                            }
                                            if (key == 'order-delivery_confirmation_date' || key == 'nameplate') {

                                                if ($this.formData['order-lav-status'].value != 'lav-status-0') {
                                                    $this.formData['order-lav-edit_pickup'] = false;
                                                    $this.toggleFlag('order-lav-edit_pickup');
                                                }
                                                editProps.push(5);
                                            }
                                            if (key == 'order-gallery') {

                                                editProps.push(6);
                                            }

                                            if (editProps.indexOf(2) != -1 && $this.formData['order-lav-status'].value != 'lav-status-0') {
                                                $this.$http.post($this.$app.api.host + '/wp-json/wp/v2/ordini/update-status', {
                                                    id: response.body.id,
                                                    meta: {
                                                        'order-lav-status': {
                                                            value: 'lav-status-0',
                                                            label: $this.formSelectOptions['order-lav-status']['lav-status-0']
                                                        },
                                                        'order-lav-user': ['lav-user-0'],
                                                        'order-lav-parts': false,
                                                        'order-lav-parts_custom': false
                                                    }
                                                }, options)
                                            }

                                            updated_meta = (!updated_meta) ? {} : updated_meta;

                                            updated_meta[key] = {
                                                'old': _old_value,
                                                'new': _new_value
                                            }
                                        }
                                    }

                                    if (editProps.length) {

                                        editProps.forEach(function(el, index) {

                                            var email_to = '',
                                                email_subject = 'Modifica ordine #' + order_id + ' - ',
                                                email_message = '';

                                            $this.options['options-edit-email'].forEach(function(el2, index2) {
                                                if (el2['edit-email-type'] == 'email-type-' + el) {

                                                    email_to = el2['edit-email-address'];
                                                    email_message = $this.user.username + ' ha apportato delle modifiche all\'ordine #' + order_id + '<br><br>';

                                                    if (el == 1) {

                                                        email_subject = email_subject + 'Data di consegna';
                                                        email_message = email_message + '<b>Data di consegna:</b> ' + $this.formData['order-date'];

                                                    }
                                                    if (el == 2) {

                                                        email_subject = email_subject + 'Dati tecnici';
                                                        email_message = email_message + '<b>Modello:</b> ' + $this.formData['order-oven_model'].label + '<br><b>Diametro:</b> ' + $this.formData['order-oven_diameter'].label + '<br><b>Finitura:</b> ' + $this.formData['order-oven_topcoat'].label + '<br><b>Bocca:</b> ' + $this.formData['order-oven_mouth'].label + '<br><b>Alimentazione:</b> ' + $this.formData['order-oven_fuel'].label + '<br><b>Lato:</b> ' + $this.formData['order-oven_fuel_side'].label;

                                                    }
                                                    if (el == 3) {

                                                        email_subject = email_subject + 'Note';
                                                        email_message = email_message + '<b>Note:</b> ' + $this.formData['order-note'];

                                                    }
                                                    if (el == 4) {

                                                        var transport = ($this.formData['order-transport']) ? 'SI' : 'NO';

                                                        email_subject = email_subject + 'Trasporto';
                                                        email_message = email_message + '<b>Trasporto ns. carico:</b> ' + transport + '<br><b>Destinazione:</b> ' + $this.formData['order-destination'] + '<br><b>Vettore:</b>' + $this.formData['order-forwarder'];

                                                    }
                                                    if (el == 5) {

                                                        email_subject = email_subject + 'Ritiro';
                                                        email_message = email_message + '<b>Data conferma ritiro</b> ' + moment($this.formData['order-delivery_confirmation_date']).format('DD/MM/YYYY') + '<br><b>Targhe:</b> ' + $this.formData['order-nameplate'];

                                                    }
                                                    if (el == 6) {

                                                        email_subject = email_subject + 'Foto';
                                                    }

                                                    email_message = email_message + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id + '<br>Storia: ' + $this.$app.urls.home + '/#/cruscotto/storia/' + response.body.id;

                                                    $this.sendMail(
                                                        $this,
                                                        'noreply@mamforni.it',
                                                        email_to,
                                                        'MAM Forni - Calendario Ordini | ' + email_subject,
                                                        email_message
                                                    );
                                                }
                                            });
                                        });
                                    }

                                    if (updated_meta !== false) {

                                        $this.updateHistory(response.body.id, {
                                            id: response.body.id,
                                            user: $this.user,
                                            date: moment().format(),
                                            action: 'edit',
                                            data: {
                                                'order-oven_id': order_id,
                                                'updated': updated_meta
                                            }
                                        });
                                    }
                                } else if($this.formData['order-type'] == 'replacement') {

                                    var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                                    if(($this.order.hasOwnProperty('id') && ($this.formData['order-replacement-size_1'] != $this.order['acf']['order-replacement-size_1'] || $this.formData['order-replacement-size_2'] != $this.order['acf']['order-replacement-size_2'] || $this.formData['order-replacement-size_3'] != $this.order['acf']['order-replacement-size_3'] || $this.formData['order-replacement-weight'] != $this.order['acf']['order-replacement-weight'])) || (!$this.order.hasOwnProperty('id') && ($this.formData['order-replacement-size_1'] || $this.formData['order-replacement-size_2'] || $this.formData['order-replacement-size_3'] || $this.formData['order-replacement-weight']))) {

                                        $this.sendMail(
                                            $this,
                                            'noreply@mamforni.it',
                                            $this.options['options-replacement-email-edit'],
                                            'MAM Forni - Calendario Ordini | Nuove specifiche per ricambio ordine #' + order_id,
                                            response.body.meta['order-user'].label + ' ha inserito nuove specifiche per il ricambio relativo all\'ordine #' + order_id + ': <br><br><b>Dimensioni collo:</b> ' + $this.formData['order-replacement-size_1'] + ' x '+ $this.formData['order-replacement-size_2'] +' h '+ $this.formData['order-replacement-size_3'] +'<br> <b>Peso:</b> ' + $this.formData['order-replacement-weight'] + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id
                                        );
                                    }
                                }

                                $this.$emit('return', {
                                    reloadOrder: true,
                                    message: 'Ordine aggiornato con successo!',
                                    status: response.ok
                                });

                            } else if (this.orderAction == 'new') {

                                if ($this.formData['order-type'] == 'order') {

                                    var order_id = (response.body.meta['order-index'] > 0) ? response.body.meta['order-oven_id'] + '-' + response.body.meta['order-index'] : response.body.meta['order-oven_id'];

                                    $this.updateHistory(response.body.id, {
                                        id: response.body.id,
                                        user: $this.user,
                                        date: moment().format(),
                                        action: 'new',
                                        data: {
                                            'order-user': response.body.meta['order-user'],
                                            'order-oven_id': order_id,
                                            'order-date': response.body.meta['order-date'],
                                            'order-lav-status': response.body.meta['order-lav-status']
                                        }
                                    });

                                    $this.options['options-lav-status_email'].forEach(function(item, index) {
                                        if (item['order-lav-status'].value == $this.formData['order-lav-status'].value) {
                                            $this.sendMail(
                                                $this,
                                                'noreply@mamforni.it',
                                                item['lav-status-email'],
                                                'MAM Forni - Calendario Ordini | Nuovo Ordine #' + order_id + ' da consegnare il ' + moment(response.body.meta['order-date']).format('DD/MM/YYYY'),
                                                response.body.meta['order-user'].label + ' ha aggiunto un nuovo ordine (#' + order_id + ') contrassegnato come <b>' + $this.formData['order-lav-status'].label + '</b> e con consegna prevista il ' + moment(response.body.meta['order-date']).format('DD/MM/YYYY') + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id + '<br>Storia: ' + $this.$app.urls.home + '/#/cruscotto/storia/' + response.body.id
                                            );
                                        }
                                    });

                                } else if($this.formData['order-type'] == 'replacement') {

                                    var order_id = (response.body.meta['order-index'] > 0) ? response.body.meta['order-oven_id'] + '-' + response.body.meta['order-index'] : response.body.meta['order-oven_id'];

                                    $this.sendMail(
                                        $this,
                                        'noreply@mamforni.it',
                                        $this.options['options-replacement-email-new'],
                                        'MAM Forni - Calendario Ordini | Nuovo ricambio per ordine #' + order_id,
                                        response.body.meta['order-user'].label + ' ha aggiunto un nuovo ricambio per l\'ordine #' + order_id + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id
                                    );

                                    $this.options['options-user-email'].forEach(function(item, index) {
                                        if(item['order-user'].value == $this.formData['order-user'].value) {
                                            $this.sendMail(
                                                $this,
                                                'noreply@mamforni.it',
                                                item['user-email'],
                                                'MAM Forni - Calendario Ordini | Nuovo ricambio per ordine #' + order_id,
                                                response.body.meta['order-user'].label + ' ha aggiunto un nuovo ricambio per l\'ordine #' + order_id + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id
                                            );
                                        }
                                    });

                                    if($this.formData['order-replacement-size_1'] || $this.formData['order-replacement-size_2'] || $this.formData['order-replacement-size_3'] || $this.formData['order-replacement-weight']) {

                                        $this.sendMail(
                                            $this,
                                            'noreply@mamforni.it',
                                            $this.options['options-replacement-email-edit'],
                                            'MAM Forni - Calendario Ordini | Nuove specifiche per ricambio ordine #' + order_id,
                                            response.body.meta['order-user'].label + ' ha inserito nuove specifiche per il ricambio relativo all\'ordine #' + order_id + ': <br><br><b>Dimensioni collo:</b> ' + $this.formData['order-replacement-size_1'] + ' x '+ $this.formData['order-replacement-size_2'] +' h '+ $this.formData['order-replacement-size_3'] +'<br> <b>Peso:</b> ' + $this.formData['order-replacement-weight'] + '<br><br>Link: ' + $this.$app.urls.home + '/#/ordine/view/' + response.body.id
                                        );
                                    }
                                }

                                $this.$emit('return', {
                                    reloadOrder: true,
                                    message: 'Ordine creato con successo!',
                                    status: response.ok
                                });
                            }

                            if (copy) {

                                var activeDay = ($this.orderAction == 'edit') ? moment($this.formData['order-date']) : moment($this.orderData);

                                var params_query = {
                                    id: ($this.orderAction == 'edit') ? $this.orderData : 0,
                                    'order-title': $this.formData['order-title'],
                                    meta: {
                                        'order-type': $this.formData['order-type'],
                                        'order-lav-status': $this.formData['order-lav-status'],
                                        'order-user': $this.formData['order-user'],
                                        'order-alert': false,
                                        'order-oven_id': $this.formData['order-oven_id'],
                                        'order-sn': $this.formData['order-sn'],
                                        'order-quote_id': $this.formData['order-quote_id'],
                                        'order-quote_date': $this.formData['order-quote_date'],
                                        'order-delivery_confirmation_date': $this.formData['order-delivery_confirmation_date'],
                                        'order-nameplate': $this.formData['order-nameplate'],
                                        'order-customer': $this.formData['order-customer'],
                                        'order-destination': $this.formData['order-destination'],
                                        'order-date': $this.formData['order-date'],
                                        'order-oven_model': $this.formData['order-oven_model'],
                                        'order-oven_diameter': $this.formData['order-oven_diameter'],
                                        'order-oven_topcoat': $this.formData['order-oven_topcoat'],
                                        'order-oven_mouth': $this.formData['order-oven_mouth'],
                                        'order-oven_fuel': $this.formData['order-oven_fuel'],
                                        'order-oven_fuel_side': $this.formData['order-oven_fuel_side'],
                                        'order-oven_etl': $this.formData['order-oven_etl'],
                                        'order-note': $this.formData['order-note'],
                                        'order-packing': $this.formData['order-packing'],
                                        'order-packing_box': $this.formData['order-packing_box'],
                                        'order-transport': $this.formData['order-transport'],
                                        'order-transport_night': $this.formData['order-transport_night'],
                                        'order-transport_user': $this.formData['order-transport_user'],
                                        'order-forwarder': $this.formData['order-forwarder'],
                                        'order-oven_provider': $this.formData['order-oven_provider'],
                                        'order-oven_provider_c1': $this.formData['order-oven_provider_c1'],
                                        'order-oven_provider_q1': $this.formData['order-oven_provider_q1'],
                                        'order-oven_provider_c2': $this.formData['order-oven_provider_c2'],
                                        'order-oven_provider_q2': $this.formData['order-oven_provider_q2'],
                                        'order-oven_provider_c3': $this.formData['order-oven_provider_c3'],
                                        'order-oven_provider_q3': $this.formData['order-oven_provider_q3'],
                                        'order-attachments_note': $this.formData['order-attachments_note'],
                                    }
                                };

                                params_query.meta['order-gallery'] = [];
                                params_query.meta['order-lav-gallery'] = [];
                                params_query.meta['order-index'] = parseInt($this.formData['order-index'], 10) + 1;
                                params_query.meta['order-index'] = (params_query.meta['order-index'] == 1) ? 2 : params_query.meta['order-index'];

                                $this.orderAction = 'new';
                                $this.$router.push({
                                    name: 'Ordine',
                                    params: {
                                        action: 'new',
                                        orderData: params_query.meta['order-date']
                                    },
                                    query: {
                                        params: JSON.stringify(params_query)
                                    }
                                });

                            } else {

                                $this.orderAction = 'view';
                                $this.$router.push({
                                    name: 'Ordine',
                                    params: {
                                        action: 'view',
                                        orderData: response.body.id
                                    }
                                });
                            }

                        }, function(error) {

                            if (this.orderAction == 'edit') {

                                $this.$emit('return', {
                                    reloadOrder: false,
                                    message: 'Impossibile aggiornare l\'ordine! (#' + error.status + ')',
                                    status: error.ok
                                });

                            } else if (this.orderAction == 'new') {

                                $this.$emit('return', {
                                    reloadOrder: false,
                                    message: 'Impossibile ceare l\'ordine! (#' + error.status + ')',
                                    status: error.ok
                                });
                            }

                        });
                    }
                });
            },
            viewStatus: function(order_id) {

                this.$router.push({
                    name: 'Stato Ordine',
                    params: {
                        orderData: order_id
                    },
                    query: {
                        source: 'Ordine'
                    }
                });
            },
            viewHistory: function(order_id) {

                this.$router.push({
                    name: 'Storia Ordine',
                    params: {
                        orderData: order_id
                    },
                    query: {
                        source: 'Ordine'
                    }
                });
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

                $this.$http.post($this.$app.urls.dist + '/api/mailer.php', params, options).then(function(response) {

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
                            message: 'Impossibile inviare la notifica! (#' + error.status + ')',
                            status: error.ok
                        });
                    }, 3000);
                });
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
                        id: this.orderData,
                        field: field,
                        value: this.formData[field]
                    };

                this.$http.post(this.$app.api.host + '/wp-json/wp/v2/ordini/toggle-flag', params, options).then(function(response) {

                    if (response.body) {

                        if (field == 'order-alert') {

                            var updateHistory_value = {
                                id: $this.order.id,
                                user: $this.user,
                                date: moment().format(),
                                action: 'toggle_flag-' + field,
                                data: {
                                    'order-oven_id': $this.order.acf['order-oven_id'],
                                }
                            };
                            updateHistory_value.data[field] = $this.formData[field];
                            $this.updateHistory($this.order.id, updateHistory_value);

                            if (this.formData[field]) {

                                $this.$emit('return', {
                                    reloadOrder: false,
                                    message: 'Ordine contrassegnato come LETTO!',
                                    status: response.ok
                                });

                            } else {

                                $this.$emit('return', {
                                    reloadOrder: false,
                                    message: 'Ordine contrassegnato come DA LEGGERE!',
                                    status: response.ok
                                });
                            }
                        }

                    }

                }, function(error) {

                    this.formData[field] = !this.formData[field];

                    /*
                    $this.$emit('return', {
                        reloadOrder: false,
                        message: 'Abbiamo riscontrato un problema! (#'+ error.status +')',
                        status: error.ok
                    });
                    */
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

                this.$http.post(this.$app.api.host + '/wp-json/wp/v2/ordini/update-history', params, options);

            },
            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },
            deleteGalleryImages: function(index) {

                var options = {
                        emulateJSON: true
                    },
                    params = {
                        id: this.orderData,
                        field: 'order-gallery',
                        images: this.formData['order-gallery'],
                        index: index
                    };

                return this.$http.post(this.$app.api.host + '/wp-json/wp/v2/ordini/delete-gallery-images', params, options);
            },
            uploadImageSuccess: function(formData, index, fileList) {
                this.formData['order-gallery'] = fileList;
            },
            beforeRemove: function(index, done, fileList) {

                var $this = this;

                if (this.orderAction == 'edit') {

                    this.deleteGalleryImages(index).then(function(response) {

                        if (!response.body.exist) {

                            $this.formData['order-gallery'] = fileList;
                            
                            return false;

                        } else if (response.body.exist && response.body.return) {

                            $this.formData['order-gallery'] = fileList;

                            var order_id = ($this.order.acf['order-index'] > 0) ? $this.order.acf['order-oven_id'] + '-' + $this.order.acf['order-index'] : $this.order.acf['order-oven_id'];

                            /*
                            $this.updateHistory($this.order.id, {
                                id: $this.order.id,
                                user: $this.user,
                                date: moment().format(),
                                action: 'edit',
                                data: {
                                    'order-oven_id': order_id,
                                    'updated': {
                                        'order-gallery': {
                                            'old' : $this.order.acf['order-gallery'],
                                            'new' : $this.formData['order-gallery']
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
                            message: 'Impossibile eliminare l\'immagine! (#' + error.status + ')',
                            status: false
                        });

                        return false;
                    });
                }
            },
            editImage: function(formData, index, fileList) {

            },
            markAsPrimary: function() {

            },
            inArray: function(array, value) {
                return array.indexOf(value) > -1 ? true : false;
            },
            print: function() {
                window.print()
            },
            htmlEntities: function(mode, _string) {

                var string = _string,
                    html_entities = {"'":"&apos;","<":"&lt;",">":"&gt;"," ":"&nbsp;","¡":"&iexcl;","¢":"&cent;","£":"&pound;","¤":"&curren;","¥":"&yen;","¦":"&brvbar;","§":"&sect;","¨":"&uml;","©":"&copy;","ª":"&ordf;","«":"&laquo;","¬":"&not;","®":"&reg;","¯":"&macr;","°":"&deg;","±":"&plusmn;","²":"&sup2;","³":"&sup3;","´":"&acute;","µ":"&micro;","¶":"&para;","·":"&middot;","¸":"&cedil;","¹":"&sup1;","º":"&ordm;","»":"&raquo;","¼":"&frac14;","½":"&frac12;","¾":"&frac34;","¿":"&iquest;","À":"&Agrave;","Á":"&Aacute;","Â":"&Acirc;","Ã":"&Atilde;","Ä":"&Auml;","Å":"&Aring;","Æ":"&AElig;","Ç":"&Ccedil;","È":"&Egrave;","É":"&Eacute;","Ê":"&Ecirc;","Ë":"&Euml;","Ì":"&Igrave;","Í":"&Iacute;","Î":"&Icirc;","Ï":"&Iuml;","Ð":"&ETH;","Ñ":"&Ntilde;","Ò":"&Ograve;","Ó":"&Oacute;","Ô":"&Ocirc;","Õ":"&Otilde;","Ö":"&Ouml;","×":"&times;","Ø":"&Oslash;","Ù":"&Ugrave;","Ú":"&Uacute;","Û":"&Ucirc;","Ü":"&Uuml;","Ý":"&Yacute;","Þ":"&THORN;","ß":"&szlig;","à":"&agrave;","á":"&aacute;","â":"&acirc;","ã":"&atilde;","ä":"&auml;","å":"&aring;","æ":"&aelig;","ç":"&ccedil;","è":"&egrave;","é":"&eacute;","ê":"&ecirc;","ë":"&euml;","ì":"&igrave;","í":"&iacute;","î":"&icirc;","ï":"&iuml;","ð":"&eth;","ñ":"&ntilde;","ò":"&ograve;","ó":"&oacute;","ô":"&ocirc;","õ":"&otilde;","ö":"&ouml;","÷":"&divide;","ø":"&oslash;","ù":"&ugrave;","ú":"&uacute;","û":"&ucirc;","ü":"&uuml;","ý":"&yacute;","þ":"&thorn;","ÿ":"&yuml;","Œ":"&OElig;","œ":"&oelig;","Š":"&Scaron;","š":"&scaron;","Ÿ":"&Yuml;","ƒ":"&fnof;","ˆ":"&circ;","˜":"&tilde;","Α":"&Alpha;","Β":"&Beta;","Γ":"&Gamma;","Δ":"&Delta;","Ε":"&Epsilon;","Ζ":"&Zeta;","Η":"&Eta;","Θ":"&Theta;","Ι":"&Iota;","Κ":"&Kappa;","Λ":"&Lambda;","Μ":"&Mu;","Ν":"&Nu;","Ξ":"&Xi;","Ο":"&Omicron;","Π":"&Pi;","Ρ":"&Rho;","Σ":"&Sigma;","Τ":"&Tau;","Υ":"&Upsilon;","Φ":"&Phi;","Χ":"&Chi;","Ψ":"&Psi;","Ω":"&Omega;","α":"&alpha;","β":"&beta;","γ":"&gamma;","δ":"&delta;","ε":"&epsilon;","ζ":"&zeta;","η":"&eta;","θ":"&theta;","ι":"&iota;","κ":"&kappa;","λ":"&lambda;","μ":"&mu;","ν":"&nu;","ξ":"&xi;","ο":"&omicron;","π":"&pi;","ρ":"&rho;","ς":"&sigmaf;","σ":"&sigma;","τ":"&tau;","υ":"&upsilon;","φ":"&phi;","χ":"&chi;","ψ":"&psi;","ω":"&omega;","ϑ":"&thetasym;","ϒ":"&Upsih;","ϖ":"&piv;","–":"&ndash;","—":"&mdash;","‘":"&lsquo;","’":"&rsquo;","‚":"&sbquo;","“":"&ldquo;","”":"&rdquo;","„":"&bdquo;","†":"&dagger;","‡":"&Dagger;","•":"&bull;","…":"&hellip;","‰":"&permil;","′":"&prime;","″":"&Prime;","‹":"&lsaquo;","›":"&rsaquo;","‾":"&oline;","⁄":"&frasl;","€":"&euro;","ℑ":"&image;","℘":"&weierp;","ℜ":"&real;","™":"&trade;","ℵ":"&alefsym;","←":"&larr;","↑":"&uarr;","→":"&rarr;","↓":"&darr;","↔":"&harr;","↵":"&crarr;","⇐":"&lArr;","⇑":"&UArr;","⇒":"&rArr;","⇓":"&dArr;","⇔":"&hArr;","∀":"&forall;","∂":"&part;","∃":"&exist;","∅":"&empty;","∇":"&nabla;","∈":"&isin;","∉":"&notin;","∋":"&ni;","∏":"&prod;","∑":"&sum;","−":"&minus;","∗":"&lowast;","√":"&radic;","∝":"&prop;","∞":"&infin;","∠":"&ang;","∧":"&and;","∨":"&or;","∩":"&cap;","∪":"&cup;","∫":"&int;","∴":"&there4;","∼":"&sim;","≅":"&cong;","≈":"&asymp;","≠":"&ne;","≡":"&equiv;","≤":"&le;","≥":"&ge;","⊂":"&sub;","⊃":"&sup;","⊄":"&nsub;","⊆":"&sube;","⊇":"&supe;","⊕":"&oplus;","⊗":"&otimes;","⊥":"&perp;","⋅":"&sdot;","⌈":"&lceil;","⌉":"&rceil;","⌊":"&lfloor;","⌋":"&rfloor;","⟨":"&lang;","⟩":"&rang;","◊":"&loz;","♠":"&spades;","♣":"&clubs;","♥":"&hearts;","♦":"&diams;"};

                if (mode == 'decode') {

                    for (var key in html_entities) {
                        var entity = html_entities[key];
                        var regex = new RegExp(entity, 'g');
                        string = string.replace(regex, key);
                    }
                    string = string.replace(/&quot;/g, '"');
                    string = string.replace(/&amp;/g, '&');

                } else if (mode == 'encode') {

                    string = string.replace(/&/g, '&amp;');
                    string = string.replace(/"/g, '&quot;');
                    for (var key in html_entities) {
                        var entity = html_entities[key];
                        var regex = new RegExp(key, 'g');
                        string = string.replace(regex, entity);
                    }
                }

                return string;
            }
        },
        filters: {
            Bool(value) {
                return (value) ? true : false
            }
        },
        components: {
            Editor,
            VueEditor,
            VueUploadMultipleImage
        }
    }

</script>
