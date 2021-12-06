<template>
    <div class="login">
       <div class="page-wrapper">
          
            <div class="loader" v-if="!pageLoaded">
                <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
            </div>
                
            <mu-container class="auth" v-if="pageLoaded">
                <mu-paper :z-depth="1">

                    <mu-form id="login-form" ref="form" :model="formData" class="login-form">

                        <mu-form-item label="username" color="var(--color-gray)" prop="login-username" :rules="formRules['login-username']">
                            <mu-text-field type="text" v-model="formData['login-username']" prop="login-username" color="var(--color-gray)"></mu-text-field>
                        </mu-form-item>

                        <mu-form-item label="password" color="var(--color-gray)" prop="login-password" :rules="formRules['login-password']">
                            <mu-text-field type="password" v-model="formData['login-password']" prop="login-password" color="var(--color-gray)"></mu-text-field>
                        </mu-form-item>

                        <mu-form-item>
                            <mu-button class="btn-login" color="var(--color-orange)" @click="login()">ACCEDI</mu-button>
                        </mu-form-item>
                    </mu-form>

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
                pageLoaded: false,
                formData: {
                    'login-username': '',
                    'login-password': ''
                },
                formRules: {
                    'login-username': [
                        { validate: (val) => !!val, message: 'Campo richiesto'}
                    ],
                    'login-password': [
                        { validate: (val) => !!val, message: 'Campo richiesto'}
                    ]
                },
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
            
            this.pageLoaded = true;
            
        },
        methods: {
            login () {
                
                var $this = this;
                
                this.$refs.form.validate().then((result) => {
                    
                    if(result) {
                        
                        this.pageLoaded = false;
                        
                        var options = {
                            emulateJSON: true
                        },
                        params = {
                            username: this.formData['login-username'],
                            password: this.formData['login-password']
                        };
                        
                        this.$http.post(this.$app.api.host +'/wp-json/wp/v2/auth/login', params, options).then(function(response) {

                            if(response.body.result) {

                                console.log(response);
                                
                                $this.$app.user = response.body.data;
                                
                                if(!$this.$session.exists()) {
                                    
                                    $this.$session.start();
                                    $this.$session.set('user', $this.$app.user);
                                    
                                    $this.openSnackBar({
                                        message: 'Benvenuto '+ $this.$app.user.username,
                                        status: true
                                    });
                                    
                                    setTimeout(() => {

                                        $this.$router.push({ name: 'Home' });

                                    }, 2000);
                                    
                                } else {
                                    
                                    setTimeout(() => {

                                        $this.$router.push({ name: 'Home' });

                                    }, 2000);
                                }
                                
                                
                            } else {
                                
                                $this.pageLoaded = true;
                                
                                $this.formData['login-password'] = '';
                            
                                $this.openSnackBar({
                                    message: 'Username o password errati',
                                    status: false
                                });
                            }
                            
                        }, function(error) {
                            
                            this.pageLoaded = true;
                            
                            $this.formData['login-password'] = '';
                            
                            $this.openSnackBar({
                                message: 'Abbiamo riscontrato un errore. Riprova.'
                            });
                            
                        });
                        
                    }
                    
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
