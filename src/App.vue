<template>
    <div id="app" :class="'view-'+ currViewSlug">
        <header class="header print-none">
            <div class="page-wrapper">
                <h1><a @click="goHome()"><img :src="appLogo" alt="MAM Forni"></a></h1>
                <h5>{{ this.$route.name }}</h5>
                
                <div class="menu">
                    <mu-button flat color="var(--color-orange)" @click="toggleSearch()">
                        <mu-icon value="search" left></mu-icon>
                        CERCA
                    </mu-button>
                    <mu-container id="search" v-if="search.toggle">
                        <mu-text-field id="search-keywords" v-model="search.value" type="text" placeholder="Cerca nella pagine" @change="findInPage"></mu-text-field>
                    </mu-container>
                    <router-link
                       v-if="this.$route.name == 'Cruscotto Ordini' || this.$route.name == 'Stato Ordine'"
                       class="menu-item" 
                       to="/calendario">
                        <mu-button flat color="var(--color-orange)">
                            <mu-icon value="today" left></mu-icon>
                            CALENDARIO
                        </mu-button>
                    </router-link>
                    <router-link 
                       v-if="this.$route.name == 'Calendario Ordini' || this.$route.name == 'Ordine'" 
                       class="menu-item" 
                       to="/cruscotto">
                        <mu-button flat color="var(--color-orange)">
                            <mu-icon value="timer" left></mu-icon>
                            CRUSCOTTO
                        </mu-button>
                    </router-link>
                    <router-link v-if="this.$app.user.role == 'guest'" class="auth" to="/login">
                        <mu-button color="var(--color-orange)">
                            <mu-icon left value="person"></mu-icon>
                            LOGIN
                        </mu-button>
                    </router-link>
                    <router-link v-else class="auth" to="/logout">
                        <mu-button color="var(--color-orange)">
                            <mu-icon left value="power_settings_new"></mu-icon>
                            LOGOUT ({{ this.$app.user.username }})
                        </mu-button>
                    </router-link>
                </div>
                
            </div>
        </header>
        
        <div class="loader" v-if="!getUser">
            <mu-circular-progress :size="36" color="var(--color-red)"></mu-circular-progress>
        </div>
        
        <router-view v-if="getUser" id="body"/>
        
        <footer class="footer print-none"></footer>
    </div>
</template>

<style>
    
    @import 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic';
    @import '../node_modules/material-design-icons/iconfont/material-icons.css';
    
    @import './assets/css/main.css';
    @import '../node_modules/muse-ui/dist/muse-ui.css';

</style>

<script>
    
    /* eslint-disable */
    
    import appLogo from './assets/images/logo.png';

    function Hilitor(id, tag) {

        // private variables
        var targetNode = document.getElementById(id) || document.body;
        var hiliteTag = tag || "MARK";
        var skipTags = new RegExp("^(?:" + hiliteTag + "|SCRIPT|FORM|SPAN)$");
        var colors = ["#ff6", "#a0ffff", "#9f9", "#f99", "#f6f"];
        var wordColor = [];
        var colorIdx = 0;
        var matchRegExp = "";
        var openLeft = false;
        var openRight = false;

        // characters to strip from start and end of the input string
        var endRegExp = new RegExp('^[^\\w]+|[^\\w]+$', "g");

        // characters used to break up the input string into words
        var breakRegExp = new RegExp('[^\\w\'-]+', "g");

        this.setEndRegExp = function(regex) {
            endRegExp = regex;
            return endRegExp;
        };

        this.setBreakRegExp = function(regex) {
            breakRegExp = regex;
            return breakRegExp;
        };

        this.setMatchType = function(type) {
            switch(type)
            {
                case "left":
                    this.openLeft = false;
                    this.openRight = true;
                    break;

                case "right":
                    this.openLeft = true;
                    this.openRight = false;
                    break;

                case "open":
                    this.openLeft = this.openRight = true;
                    break;

                default:
                    this.openLeft = this.openRight = false;

            }
        };

        this.setRegex = function(input) {
            input = input.replace(endRegExp, "");
            input = input.replace(breakRegExp, "|");
            input = input.replace(/^\||\|$/g, "");
            if(input) {
                var re = "(" + input + ")";
                if(!this.openLeft) {
                    re = "\\b" + re;
                }
                if(!this.openRight) {
                    re = re + "\\b";
                }
                matchRegExp = new RegExp(re, "i");
                return matchRegExp;
            }
            return false;
        };

        this.getRegex = function() {
            var retval = matchRegExp.toString();
            retval = retval.replace(/(^\/(\\b)?|\(|\)|(\\b)?\/i$)/g, "");
            retval = retval.replace(/\|/g, " ");
            return retval;
        };

        // recursively apply word highlighting
        this.hiliteWords = function(node) {
            if(node === undefined || !node) return;
            if(!matchRegExp) return;
            if(skipTags.test(node.nodeName)) return;

            if(node.hasChildNodes()) {
                for(var i=0; i < node.childNodes.length; i++)
                    this.hiliteWords(node.childNodes[i]);
            }
            if(node.nodeType == 3) { // NODE_TEXT
                var nv, regs;
                if((nv = node.nodeValue) && (regs = matchRegExp.exec(nv))) {
                    if(!wordColor[regs[0].toLowerCase()]) {
                        wordColor[regs[0].toLowerCase()] = colors[colorIdx++ % colors.length];
                    }

                    var match = document.createElement(hiliteTag);
                    match.appendChild(document.createTextNode(regs[0]));
                    match.style.backgroundColor = wordColor[regs[0].toLowerCase()];
                    match.style.color = "#000";

                    var after = node.splitText(regs.index);
                    after.nodeValue = after.nodeValue.substring(regs[0].length);
                    node.parentNode.insertBefore(match, after);
                }
            }
        };

        // remove highlighting
        this.remove = function() {
            var arr = document.getElementsByTagName(hiliteTag), el;
            while(arr.length && (el = arr[0])) {
                var parent = el.parentNode;
                parent.replaceChild(el.firstChild, el);
                parent.normalize();
            }
        };

        // start highlighting at target node
        this.apply = function(input) {
            this.remove();
            if(input === undefined || !(input = input.replace(/(^\s+|\s+$)/g, ""))) {
                return;
            }
            if(this.setRegex(input)) {
                this.hiliteWords(targetNode);
            }
            return matchRegExp;
        };

    }
    
    export default {
        name: 'App',
        data() {
            return {
                appLogo: appLogo,
                getUser: false,
                currViewSlug: this.$route.name.toLowerCase(),
                search: {
                    el: false,
                    toggle: false,
                    value: ''
                }
            }
        },
        watch: {
            $route: function() {
                this.currViewSlug = this.$route.name.toLowerCase();
            }
        },
        created() {
            
            var $this = this;

            this.search.el = new Hilitor("body");
            this.search.el.setMatchType("left");
            
            this.$storageConfig({
                name: 'app',
                storeName: 'db'
            });
            
            this.getCurrentUser();
            /*console.log(this.$app.user.role);
            if (this.$app.user.role === 'guest' && this.$route.name !== 'Login') {
                this.$router.push({ name: 'Login' });
            }*/
        },
        methods: {
            getCurrentUser: function() {
                
                this.getUser = true;
                
                if(this.$session.exists()) {
                    this.$app.user = this.$session.get('user');
                }
            },
            goHome: function() {
                this.$router.push({ name: this.$route.name });
            },
            toggleSearch: function() {
                this.search.toggle = !this.search.toggle;
            },
            findInPage: function () {
                if(this.search.el) this.search.el.apply(this.search.value);
                else console.error('Hilitor is not defined!');
            }
        }
    }
</script>
