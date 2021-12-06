<template>
    <div class="logout">
       <div class="page-wrapper">
          
            <div class="loader" v-if="!pageLoaded">
                <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
            </div>
                
            <mu-container class="auth" v-if="pageLoaded">
                <mu-paper :z-depth="1">

                </mu-paper>
            </mu-container>
            
            <mu-snackbar :color="(snackBar.return) ? 'success' : 'error'" :open.sync="snackBar.open">
                <mu-icon left :value="(snackBar.return) ? 'check_circle' : 'warning'"></mu-icon>
                {{snackBar.message}}
                <mu-button flat slot="action" color="#fff" @click="closeSnackBar()">CHIUDI</mu-button>
            </mu-snackbar>
            
        </div>
    </div>
</template>

<style scoped>
    
    @import '../../assets/css/views/auth.css';

</style>

<script>
    
    /* eslint-disable */

    export default {
        name: 'AuthLogin',
        data() {
            return {
                user: this.$app.user,
                pageLoaded: false,
                snackBar: {
                    open: false,
                    timer: false,
                    message: '',
                    return: false
                }
            }
        },
        watch: {
            
        },
        mounted() {
            
        },
        created() {
            
            this.logout();
            
        },
        methods: {
            logout () {
                
                var $this = this;
                
                var options = {
                        emulateJSON: true
                    },
                    params = {
                        'id': $this.$app.user.id
                    };

                this.$http.post(this.$app.api.host +'/wp-json/wp/v2/auth/logout', params, options).then(function(response) {
                    
                    if(response.body.result) {
                        
                        $this.$app.user = response.body.data;
                        
                        if($this.$session.exists()) {
                            
                            $this.$session.clear();
                            $this.$session.destroy();
                            
                            $this.openSnackBar({
                                message: 'Logout effettutato con successo',
                                status: true
                            });

                            setTimeout(() => {

                                $this.$router.push({ name: 'Login' });

                            }, 2000);
                            
                        } else {
                            
                            $this.openSnackBar({
                                message: 'Impossibile effettuare il logout. Effettua il login e riprova.',
                                status: false
                            });
                        }
                        
                    } else {
                        
                        $this.openSnackBar({
                            message: 'Impossibile effettuare il logout. Riprova.'
                        });
                    }
                    
                }, function(error) {
                    
                    $this.openSnackBar({
                        message: 'Impossibile effettuare il logout. Riprova.'
                    });
                });
                
            },
            openSnackBar: function(response) {
                
                this.snackBar.open = true;
                
                if(this.snackBar.timer) clearTimeout(this.snackBar.timer);
                this.snackBar.timer = setTimeout(() => {
                    
                    this.snackBar.open = false;
                    
                }, 3000);
                
                this.snackBar.message = response.message;
                this.snackBar.return = response.status;
                
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
