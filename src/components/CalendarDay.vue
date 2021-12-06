<!-- eslint-disable vue/no-use-v-if-with-v-for,vue/no-confusing-v-for-v-if -->

<template>

    <mu-container class="calendar-day" :class="{ 'is-festivity': isFestivity, 'is-weekend': dayOfWeek == 'sab' || dayOfWeek == 'dom' }">
        <mu-card :raised="isToday">
            <mu-card-header class="calendar-day-head" :title="currDate | moment('DD ddd')"></mu-card-header>
            <mu-card-text class="calendar-day-body">

                <div class="row row-orders">
                    <mu-ripple v-for="(currOrder, index) in orderData" :key="'key-id-'+ index" v-if="currOrder['acf']['order-type'] == 'order' && !currOrder['acf']['order-transport']" @click="openOrderDialog(currOrder)" class="calendar-day-single type-order" :class="[''+currOrder['acf']['order-packing']['value'], {'with-transport': currOrder['acf']['order-transport']}]">
                        <div class="calendar-day-single-content">
                            {{ currOrder['acf']['order-oven_id'] }}<span v-if="currOrder['acf']['order-index'] > 0">-{{ currOrder['acf']['order-index'] }}</span>
                        </div>
                        <mu-badge :content="getOrderLavStatusIcon(currOrder['acf']['order-lav-status'])" class="print-none" :color="(isFestivity) ? 'var(--color-yellow)' : 'var(--color-orange)'"></mu-badge>
                    </mu-ripple>
                </div>

                <div class="row row-notes row-orders-note">
                    <mu-ripple v-for="(currOrder, index) in orderData" :key="'key-id-'+ index" v-if="currOrder['acf']['order-transport']" class="calendar-day-single type-note" :class="[''+currOrder['acf']['order-packing']['value'], {'with-transport': currOrder['acf']['order-transport'], 'clone': currOrder['acf']['order-date'] != currDate}]" @click="openOrderDialog(currOrder)">
                        <span class="calendar-day-single-content-id">{{ currOrder['acf']['order-oven_id'] }}<span v-if="currOrder['acf']['order-index'] > 0">-{{ currOrder['acf']['order-index'] }}</span></span>
                        <span class="calendar-day-single-content" v-if="currOrder['acf']['order-date'] == currDate">
                            <b>{{ currOrder['acf']['order-destination'] }}</b> |
                            {{ currOrder['acf']['order-oven_model']['label'] }} - {{ currOrder['acf']['order-oven_diameter']['label'] }} - {{ currOrder['acf']['order-oven_topcoat']['label'] }} - {{ currOrder['acf']['order-oven_mouth']['label'] }} - {{ currOrder['acf']['order-oven_fuel']['label'] }} - {{ currOrder['acf']['order-oven_fuel_side']['label'] }} {{ (currOrder['acf']['order-transport_user']) ? ' | ' : '' }}
                            <span v-if="currOrder['acf']['order-transport_user']" v-for="(value, key, index) in currOrder['acf']['order-transport_user']" :key="index">{{ value['label'] +', ' }}</span>
                        </span>
                        <span class="calendar-day-single-content" v-if="currOrder['acf']['order-date'] != currDate">
                            <b>(T) {{ currOrder['acf']['order-destination'] }}</b> - <span v-if="currOrder['acf']['order-transport_user']" v-for="(value, key, index) in currOrder['acf']['order-transport_user']" :key="index">{{ value['label'] +', ' }}</span>
                        </span>
                        <mu-badge :content="getOrderLavStatusIcon(currOrder['acf']['order-lav-status'])" class="print-none" :color="(isFestivity) ? 'var(--color-yellow)' : 'var(--color-orange)'"></mu-badge>
                    </mu-ripple>
                </div>

                <div class="row row-notes row-trips">
                    <mu-ripple v-for="(currTrip, index) in tripData" :key="'key-id-'+ index" class="calendar-day-single type-note" :class="[currTrip['acf']['order-packing']['value'], {'clone': currTrip['acf']['order-date'] != currDate}]" @click="openOrderDialog(currTrip)">
                        <span class="calendar-day-single-content-id">{{ currTrip['acf']['order-oven_id'] }}<span v-if="currTrip['acf']['order-index'] > 0">-{{ currTrip['acf']['order-index'] }}</span></span>
                        <span class="calendar-day-single-content">
                            <b>{{ currTrip['acf']['order-destination'] }}</b> - <span v-if="currTrip['acf']['order-transport_user']" v-for="(value, key, index) in currTrip['acf']['order-transport_user']" :key="index">{{ value['label'] +', ' }}</span> | <span v-html="getExcerpt(currTrip['acf']['order-note'])"></span>
                        </span>
                    </mu-ripple>
                </div>

                <div class="row row-notes row-replacements print-none">
                    <mu-ripple v-for="(currReplacement, index) in replacementData" :key="'key-id-'+ index" class="calendar-day-single type-note" @click="openOrderDialog(currReplacement)">
                        <span class="calendar-day-single-content-id">{{ currReplacement['acf']['order-oven_id'] }}<span v-if="currReplacement['acf']['order-index'] > 0">-{{ currReplacement['acf']['order-index'] }}</span></span>
                        <span class="calendar-day-single-content">
                            <span v-for="(value, key) in currReplacement['acf']['order-replacement-parts']" :key="key" v-if="value['label'] != 'Altro'">{{ value['label'] +', ' }}</span> <span v-if="currReplacement['acf']['order-replacement-parts_custom'] && inArray(currReplacement['acf']['order-replacement-parts'], 'replacement-parts-0')">{{ currReplacement['acf']['order-replacement-parts_custom'] }}</span> | <span v-html="getExcerpt(currReplacement['acf']['order-customer'])"></span>
                        </span>
                    </mu-ripple>
                </div>

                <div class="row row-notes">
                    <mu-ripple v-for="(currNote, index) in noteData" :key="'key-id-'+ index" class="calendar-day-single type-note" @click="openOrderDialog(currNote)">
                        <span class="calendar-day-single-content" v-html="getExcerpt(currNote['acf']['order-note'])"></span>
                        <!--<mu-badge content=""
                                  class="print-none"
                                  :color="(isFestivity) ? 'var(--color-yellow)' : 'var(--color-orange)'"
                                  v-if="!currNote['acf']['order-alert']"
                                  ></mu-badge>-->
                    </mu-ripple>
                </div>

            </mu-card-text>
            <mu-card-actions class="calendar-day-action print-none" v-if="user.role == 'admin'">
                <mu-button fab :color="(isFestivity) ? 'var(--color-yellow)' : 'var(--color-orange)'" @click="openOrderDialog()" small>
                    <mu-icon value="add"></mu-icon>
                </mu-button>
            </mu-card-actions>
        </mu-card>
    </mu-container>
</template>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    @import '../assets/css/components/calendarDay.css';

</style>

<script>
    /* eslint-disable */

    const moment = require('moment');

    export default {
        name: 'CalendarDay',
        props: {
            day: {},
            orders: {},
            notes: {},
            trips: {},
            replacements: {},
            isFestivity: false,
            dayOfWeek: '',
            user: {
                role: 'guest'
            }
        },
        data() {
            return {
                moment: moment,
                isToday: false,
                currDate: this.day.format(),
                orderData: this.orders,
                noteData: this.notes,
                tripData: this.trips,
                replacementData: this.replacements
            }
        },
        watch: {
            orders: function(orders) {
                this.orderData = orders
            },
            notes: function(notes) {
                this.noteData = notes
            },
            trips: function(trips) {
                this.tripData = trips
            },
            replacements: function(replacements) {
                this.replacementData = replacements
            },
            day: function() {
                this.init();
            }
        },
        created() {
            this.init();
        },
        methods: {
            init: function() {
                this.currDate = this.day.format();
                this.isToday = (moment(this.currDate).format('DD-MM-YYYY') == moment().format('DD-MM-YYYY')) ? true : false;
            },
            isJustUpdated: function(order) {

                var todayDate = moment(),
                    lastUpdateDate = moment(order.modified);

                if (todayDate.diff(lastUpdateDate, 'days') < 1) return true;

                return false;
            },
            openOrderDialog: function(order) {

                if (order !== undefined) {

                    this.$router.push({
                        name: 'Ordine',
                        params: {
                            action: 'view',
                            orderData: order.ID
                        }
                    });

                } else {

                    const date = this.currDate;

                    this.$router.push({
                        name: 'Ordine',
                        params: {
                            action: 'new',
                            orderData: date
                        }
                    });
                }
            },
            getExcerpt: function(content) {
                content = content.replace('<br>', ' ');
                content = content.replace(/<[^>]+>/g, '');
                return content.substring(0, 120);
            },
            getOrderLavStatusIcon: function(orderLavStatus) {
                switch (orderLavStatus.value) {
                    case 'lav-status-0':
                        return 'N';
                        break;
                    case 'lav-status-1':
                        return 'C';
                        break;
                    case 'lav-status-2':
                        return 'O';
                        break;
                    case 'lav-status-3':
                        return 'L';
                        break;
                    case 'lav-status-4':
                        return 'F';
                        break;
                    case 'lav-status-5':
                        return 'S';
                        break;
                    default:
                        return 'N';
                }
            },
            inArray: function(array, value) {
                if(array.length && typeof array[0] == 'object') {

                    var exist = false;
                    array.forEach(function(el, i) {
                        if(el.value == value) exist = true;
                    });
                    return exist;

                } else {

                    return array.indexOf(value) > -1 ? true : false;
                }
            },
        }
    }

</script>
