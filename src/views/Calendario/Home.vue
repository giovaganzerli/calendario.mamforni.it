<template>
    <div class="home">
        <div class="page-wrapper">

            <div class="loader" v-if="!pageLoaded">
                <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
            </div>

            <div v-if="pageLoaded">

                <OrderDialog :action="orderDialogAction" :orderData="orderDialogData" :user="user" @return="openSnackBar" />

                <mu-container class="calendar">

                    <div class="calendar-head print-none">
                        <mu-date-input icon="today" type="month" label="Seleziona Mese / Anno" format="MM / YYYY" :date-time-format="itDateFormat" v-model="activeDate" @change="switchDate()" color="var(--color-orange)" display-color="var(--color-orange)" label-float landscape></mu-date-input>
                        <mu-button color="var(--color-orange)" textColor="var(--color-white)" style="margin-left:20px;" @click="goToday()" small>OGGI</mu-button>
                        <mu-button class="btn-print" color="var(--color-orange)" textColor="var(--color-white)" @click="print()" small>
                            <mu-icon left value="print"></mu-icon>STAMPA
                        </mu-button>
                    </div>

                    <div class="calendar-body">
                        <h4 class="col-title print-block">{{ activeDate | moment("MMMM YYYY") }}</h4>
                        <div class="col-content">
                            <CalendarDay v-for="(currDay, index) in currMonthDays" :key="index" :day="currDay" :isFestivity="isFestivity(currDay)" :dayOfWeek="currDay | moment('ddd')" :user="user" :orders="getInDayOrders(currDay)" :notes="getInDayNotes(currDay)" :trips="getInDayTrips(currDay)" :replacements="getInDayReplacements(currDay)" />
                        </div>
                    </div>

                </mu-container>
            </div>

            <mu-snackbar color="success" :open="pageLoaded && !ordersLoaded" position="bottom-end" style="min-width: 0px;">
                <mu-circular-progress :size="20" color="var(--color-white)" style="margin-right: 10px;"></mu-circular-progress>
                AGGIORNO...
            </mu-snackbar>

            <mu-snackbar :color="(snackBar.return) ? 'success' : 'error'" :open.sync="snackBar.open">
                <mu-icon left :value="(snackBar.return) ? 'check_circle' : 'warning'"></mu-icon>
                {{snackBar.message}}
                <mu-button flat slot="action" color="#fff" @click="closeSnackBar()">CHIUDI</mu-button>
            </mu-snackbar>

        </div>
    </div>
</template>

<style scoped>
    @import '../../assets/css/views/calendario-home.css';

</style>

<script>
    /* eslint-disable */

    // @ is an alias to /src
    import CalendarDay from '@/components/CalendarDay.vue'
    import OrderDialog from '@/components/OrderDialog.vue'

    const monthList = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
    const monthLongList = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];

    const itDateFormat = {
        formatDisplay(date) {
            return "" + dayList[date.getDay()] + ", " + monthList[date.getMonth()] + " " + date.getDate();
        },
        formatMonth(date) {
            return "" + monthLongList[date.getMonth()] + " " + date.getFullYear();
        },
        getMonthList() {
            return monthList;
        }
    };

    export default {
        name: 'CalendarioHome',
        data() {
            return {
                user: this.$app.user,
                itDateFormat,
                pageLoaded: false,
                ordersLoaded: false,
                notesLoaded: false,
                tripsLoaded: false,
                replacementsLoaded: false,
                reloadInterval: false,
                activeDate: this.$moment(),
                options: {},
                currMonthDays: [],
                orderDialogAction: false,
                orderDialogData: false,
                orders: {},
                notes: {},
                trips: {},
                replacements: {},
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

                this.initOrderDialog(this.$route);

                if (this.$route.name == 'Calendario Ordini') {

                    this.reloadInterval = false;
                    this.getOptions();

                } else {

                    clearInterval(this.reloadInterval);
                    this.ordersLoaded = true;
                }
            }
        },
        mounted() {
            this.initOrderDialog(this.$route);
        },
        beforeDestroy() {
            clearInterval(this.reloadInterval);
            this.ordersLoaded = true;
        },
        created() {

            var $this = this;

            this.currMonthDays = this.getDaysInMonth(this.activeDate);

            this.getCurrentUser();
            this.getOptions();

            this.getOrders({
                month: this.activeDate.format('MM'),
                year: this.activeDate.format('YYYY')
            });
            this.getNotes({
                month: this.activeDate.format('MM'),
                year: this.activeDate.format('YYYY')
            });
            this.getTrips({
                month: this.activeDate.format('MM'),
                year: this.activeDate.format('YYYY')
            });
            this.getReplacements({
                month: this.activeDate.format('MM'),
                year: this.activeDate.format('YYYY')
            });
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

                    if ($this.$route.name == 'Calendario Ordini' && !$this.reloadInterval) {
                        $this.reloadInterval = setInterval(function() {

                            $this.ordersLoaded = false;
                            $this.notesLoaded = false;
                            $this.tripsLoaded = false;
                            $this.replacementsLoaded = false;
                            $this.getOrders({
                                month: $this.activeDate.format('MM'),
                                year: $this.activeDate.format('YYYY')
                            });
                            $this.getNotes({
                                month: $this.activeDate.format('MM'),
                                year: $this.activeDate.format('YYYY')
                            });
                            $this.getTrips({
                                month: $this.activeDate.format('MM'),
                                year: $this.activeDate.format('YYYY')
                            });
                            $this.getReplacements({
                                month: $this.activeDate.format('MM'),
                                year: $this.activeDate.format('YYYY')
                            });

                        }, $this.options['options-updating_time']);
                    }

                });
            },
            getOrders: function(date) {

                var $this = this;

                var params = {
                        'post_type': 'ordini',
                        'posts_per_page': -1,
                        'no_found_rows' : 'true',
                        'update_post_term_cache' : 'false',
                        'update_post_meta_cache' : 'false',
                        'cache_results' : 'false',
                        'orderby': 'date',
                        'order': 'DESC',
                        'meta_query' : [{
                            'relation': 'AND',
                            1: {
                                'key': 'order-type',
                                'value': 'order',
                                'compare': '='
                            },
                            2: [{
                                'relation': 'OR',
                                1: {
                                    'key': 'order-date',
                                    'value': [date.year + '-' + date.month + '-00T00:00:00+00:00', date.year + '-' + date.month + '-32T23:59:59+00:00'],
                                    'compare' : 'BETWEEN'
                                },
                                2: {
                                    'key': 'order-delivery_confirmation_date',
                                    'value': [date.year + '-' + date.month + '-00T00:00:00+00:00', date.year + '-' + date.month + '-32T23:59:59+00:00'],
                                    'compare' : 'BETWEEN'
                                }
                            }]
                        }]
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        meta: [
                            'order-type',
                            'order-transport',
                            'order-packing',
                            'order-oven_id',
                            'order-index',
                            'order-lav-status',
                            'order-date',
                            'order-destination',
                            'order-oven_model',
                            'order-oven_diameter',
                            'order-oven_topcoat',
                            'order-oven_mouth',
                            'order-oven_fuel',
                            'order-oven_fuel_side',
                            'order-transport_user',
                            'order-transport_night',
                            'order-delivery_confirmation_date',
                            'order-note',
                            'order-alert'
                        ]
                    };

                if (!this.ordersLoaded) {
                    this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                        data: {
                            'parametri': params,
                            'opzioni': options,
                            'chiave' : 'ordini_calendario'
                        }
                    }).then(function(response) {

                        $this.ordersLoaded = true;
                        $this.pageLoaded = ($this.tripsLoaded && $this.notesLoaded && $this.replacementsLoaded) ? true : $this.pageLoaded;

                        $this.orders = response.body;
                    });
                }
            },
            getNotes: function(date) {

                var $this = this;

                var params = {
                        'post_type': 'ordini',
                        'posts_per_page': -1,
                        'no_found_rows': 'true',
                        'update_post_term_cache': 'false',
                        'update_post_meta_cache': 'false',
                        'cache_results': 'false',
                        'orderby': 'date',
                        'order': 'DESC',
                        'meta_query': {
                            'relation': 'AND',
                            1: {
                                'key': 'order-type',
                                'value': 'note',
                                'compare': '='
                            },
                            2: {
                                'key': 'order-date',
                                'value': date.year + '-' + date.month + '-00T00:00:00+00:00',
                                'compare' : '>='
                            },
                            3: {
                                'key': 'order-date',
                                'value': date.year + '-' + date.month + '-32T23:59:59+00:00',
                                'compare': '<='
                            }
                        }
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        'meta' : [
                            'order-type',
                            'order-oven_id',
                            'order-index',
                            'order-date',
                            'order-note',
                            'order-alert'
                        ]
                    };

                if (!this.notesLoaded) {
                    this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                        data: {
                            'parametri': params,
                            'opzioni': options,
                            'chiave' : 'note_calendario'
                        }
                    }).then(function(response) {

                        $this.notesLoaded = true;
                        $this.pageLoaded = ($this.ordersLoaded && $this.tripsLoaded && $this.replacementsLoaded) ? true : $this.pageLoaded;

                        $this.notes = response.body;
                    });
                }
            },
            getTrips: function(date) {
                var $this = this;

                var params = {
                        'post_type': 'ordini',
                        'posts_per_page': -1,
                        'no_found_rows' : 'true',
                        'update_post_term_cache': 'false',
                        'update_post_meta_cache': 'false',
                        'cache_results': 'false',
                        'orderby': 'date',
                        'order': 'DESC',
                        'meta_query': {
                            1: {
                                'key': 'order-type',
                                'value': 'trip',
                                'compare': '='
                            }
                        }
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        'meta' : [
                            'order-packing',
                            'order-date',
                            'order-oven_id',
                            'order-index',
                            'order-destination',
                            'order-transport_night',
                            'order-transport_user',
                            'order-note'
                        ]
                    };

                if (!this.tripsLoaded) {
                    this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                        data: {
                            'parametri': params,
                            'opzioni': options,
                            'chiave' : 'trips_calendario'
                        }
                    }).then(function(response) {

                        $this.tripsLoaded = true;
                        $this.pageLoaded = ($this.ordersLoaded && $this.notesLoaded && $this.replacementsLoaded) ? true : $this.pageLoaded;

                        $this.trips = response.body.filter(function(trip) {
                            var start_date = parseInt($this.activeDate.format('YYYYMM01')),
                                end_date = parseInt($this.activeDate.format('YYYYMM31')),
                                trip_date_start = parseInt($this.$moment(trip.acf['order-date'], 'YYYY-MM-DD').format('YYYYMMDD')),
                                trip_date_end = parseInt($this.$moment(trip.acf['order-date'], 'YYYY-MM-DD').add(trip.acf['order-transport_night'], 'days').format('YYYYMMDD'));
                            return (trip_date_start >= start_date && trip_date_start <= end_date) || (trip_date_end >= start_date && trip_date_end <= end_date);
                        });
                    });
                }
            },
            getReplacements: function(date) {
                var $this = this;

                var params = {
                        'post_type': 'ordini',
                        'posts_per_page': -1,
                        'no_found_rows' : 'true',
                        'update_post_term_cache': 'false',
                        'update_post_meta_cache': 'false',
                        'cache_results': 'false',
                        'orderby': 'date',
                        'order': 'DESC',
                        'meta_query': {
                            1: {
                                'key': 'order-type',
                                'value': 'replacement',
                                'compare': '='
                            }
                        }
                    },
                    options = {
                        fields: [
                            'ID'
                        ],
                        'meta' : [
                            'order-date',
                            'order-user',
                            'order-index',
                            'order-oven_id',
                            'order-index',
                            'order-customer',
                            'order-replacement-parts',
                            'order-replacement-parts_custom',
                            'order-replacement-size',
                            'order-replacement-neck',
                            'order-replacement-weight',
                            'order-gallery'
                        ]
                    };

                if (!this.replacementsLoaded) {
                    this.$http.post(this.$app.api.host + '/wp-json/wp/v2/query', {
                        data: {
                            'parametri': params,
                            'opzioni': options,
                            'chiave' : 'replacements_calendario'
                        }
                    }).then(function(response) {

                        $this.replacementsLoaded = true;
                        $this.pageLoaded = ($this.ordersLoaded && $this.notesLoaded && $this.tripsLoaded) ? true : $this.pageLoaded;

                        $this.replacements = response.body;
                    });
                }
            },
            getInDayOrders: function(date) {
                var $this = this;
                return this.orders.filter(function(order) {
                    if (order['acf']['order-transport'] && parseInt(order['acf']['order-transport_night'])) {
                        var currDate = parseInt(date.format('YYYYMMDD')),
                            trip_date_start = parseInt($this.$moment(order.acf['order-date'], 'YYYY-MM-DD').format('YYYYMMDD')),
                            trip_date_end = parseInt($this.$moment(order.acf['order-date'], 'YYYY-MM-DD').add(order.acf['order-transport_night'], 'days').format('YYYYMMDD'));
                        return currDate >= trip_date_start && currDate <= trip_date_end
                    } else {
                        if (order['acf']['order-delivery_confirmation_date']) {
                            return $this.$moment(order['acf']['order-delivery_confirmation_date'], 'YYYY-MM-DD').format('DD-MM-YYYY') == date.format('DD-MM-YYYY');
                        } else {
                            return $this.$moment(order['acf']['order-date'], 'YYYY-MM-DD').format('DD-MM-YYYY') == date.format('DD-MM-YYYY');
                        }
                    }
                });
            },
            getInDayNotes: function(date) {
                var $this = this;
                return this.notes.filter(function(note) {
                    return $this.$moment(note['acf']['order-date'], 'YYYY-MM-DD').format('DD-MM-YYYY') == date.format('DD-MM-YYYY');
                });
            },
            getInDayTrips: function(date) {
                var $this = this;
                return this.trips.filter(function(trip) {
                    var currDate = parseInt(date.format('YYYYMMDD')),
                        trip_date_start = parseInt($this.$moment(trip.acf['order-date'], 'YYYY-MM-DD').format('YYYYMMDD')),
                        trip_date_end = parseInt($this.$moment(trip.acf['order-date'], 'YYYY-MM-DD').add(trip.acf['order-transport_night'], 'days').format('YYYYMMDD'));
                    return currDate >= trip_date_start && currDate <= trip_date_end
                });
            },
            getInDayReplacements: function(date) {
                var $this = this;
                return this.replacements.filter(function(replacement) {
                    return $this.$moment(replacement['acf']['order-date'], 'YYYY-MM-DD').format('DD-MM-YYYY') == date.format('DD-MM-YYYY');
                });
            },
            getDaysInMonth: function(date) {

                var month = date.format('M'),
                    year = date.format('YYYY');

                var daysNum = this.$moment([year, month - 1, 1]).daysInMonth(),
                    days = [];

                for (var day = 1; day <= daysNum; day++) {
                    days.push(this.$moment([year, month - 1, day, 0, 0, 0, 0]));
                }

                return days;
            },
            isFestivity: function(currDate) {

                var $this = this,
                    $return = false;

                if (currDate.format('ddd') == 'dom' || currDate.format('ddd') == 'sab') {
                    $return = true;
                } else {
                    this.options['options-festivities'].forEach(function(item, index) {
                        var _currDate = currDate.format().toString().split('T'),
                            _festivityDate = item['festivity-date'].split('T');
                        if (_currDate[0] == _festivityDate[0]) $return = true;
                    });
                }

                return $return
            },
            switchDate: function() {

                this.ordersLoaded = false;
                this.notesLoaded = false;
                this.tripsLoaded = false;
                this.replacementsLoaded = false;

                this.activeDate = this.$moment(this.activeDate);
                this.currMonthDays = this.getDaysInMonth(this.activeDate);

                this.getOrders({
                    month: this.activeDate.format('MM'),
                    year: this.activeDate.format('YYYY')
                });
                this.getNotes({
                    month: this.activeDate.format('MM'),
                    year: this.activeDate.format('YYYY')
                });
                this.getTrips({
                    month: this.activeDate.format('MM'),
                    year: this.activeDate.format('YYYY')
                });
                this.getReplacements({
                    month: this.activeDate.format('MM'),
                    year: this.activeDate.format('YYYY')
                });
            },
            goToday: function() {

                this.activeDate = this.$moment();

                this.switchDate();
            },
            initOrderDialog: function(currRoute) {
                if (currRoute.name == 'Ordine') {

                    this.orderDialogAction = currRoute.params.action;
                    this.orderDialogData = currRoute.params.orderData;

                } else {

                    this.orderDialogAction = false;
                    this.orderDialogData = false;
                }
            },
            openSnackBar: function(response) {

                this.snackBar.open = true;

                if (this.snackBar.timer) clearTimeout(this.snackBar.timer);
                this.snackBar.timer = setTimeout(() => {

                    this.snackBar.open = false;

                }, 3000);

                this.snackBar.message = response.message;
                this.snackBar.return = response.status;

                if (response.reloadOrder) {
                    this.ordersLoaded = false;
                    this.notesLoaded = false;
                    this.tripsLoaded = false;
                    this.replacementsLoaded = false;
                    this.getOrders({
                        month: this.activeDate.format('MM'),
                        year: this.activeDate.format('YYYY')
                    });
                    this.getNotes({
                        month: this.activeDate.format('MM'),
                        year: this.activeDate.format('YYYY')
                    });
                    this.getTrips({
                        month: this.activeDate.format('MM'),
                        year: this.activeDate.format('YYYY')
                    });
                    this.getReplacements({
                        month: this.activeDate.format('MM'),
                        year: this.activeDate.format('YYYY')
                    });
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
            CalendarDay,
            OrderDialog
        }
    }

</script>
