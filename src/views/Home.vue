<template>
    <div class="home-landing">
       <div class="page-wrapper">
          
            <router-link to="/calendario" class="section-selector">
                <mu-button color="var(--color-orange)">
                    <mu-icon value="today" left></mu-icon>
                    CALENDARIO ORDINI
                </mu-button>
            </router-link>
            
            <router-link to="/cruscotto" class="section-selector">
                <mu-button color="var(--color-orange)">
                    <mu-icon value="timer" left></mu-icon>
                    CRUSCOTTO ORDINI
                </mu-button>
            </router-link>
            
            <mu-snackbar :color="(snackBar.return) ? 'success' : 'error'" :open.sync="snackBar.open">
                <mu-icon left :value="(snackBar.return) ? 'check_circle' : 'warning'"></mu-icon>
                {{snackBar.message}}
                <mu-button flat slot="action" color="#fff" @click="closeSnackBar()">CHIUDI</mu-button>
            </mu-snackbar>
            
        </div>
    </div>
</template>

<style scoped>
    
    @import '../assets/css/views/home.css';

</style>

<script>
    
    /* eslint-disable */

    export default {
        name: 'Home',
        data() {
            return {
                user: this.$app.user,
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
                
            }
        },
        mounted() {
            
        },
        created() {
            
            this.getCurrentUser();
            
        },
        methods: {
            getCurrentUser: function() {
                
                if(this.$session.exists()) {
                    this.$app.user = this.$session.get('user');
                }
            },
            openSnackBar: function(response) {
                
                this.snackBar.open = true;
                
                if(this.snackBar.timer) clearTimeout(this.snackBar.timer);
                this.snackBar.timer = setTimeout(() => {
                    
                    this.snackBar.open = false;
                    
                }, 3000);
                
                this.snackBar.message = response.message;
                this.snackBar.return = response.status;
                
                if(response.reloadOrder) this.getOrders(this);
            },
            closeSnackBar: function() {
                
                if(this.snackBar.timer) clearTimeout(this.snackBar.timer);
                this.snackBar.open = false;
            }
        },
        components: {
        }
    }

</script>
