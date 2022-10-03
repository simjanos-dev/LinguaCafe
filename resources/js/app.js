/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Vue = require('vue').default;
var VueCookie = require('vue-cookie');
window.Vue.use(VueCookie);
import axios from 'axios'
import Vue from 'vue';
import VueCircle from 'vue2-circle-progress';
import VueSlider from 'vue-slider-component';

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
Vue.component('vue-circle', VueCircle);
Vue.component('vue-slider', VueSlider);
Vue.component('reader-component', require('./components/ReaderComponent.vue').default);
Vue.component('ebook-reader-mode-component', require('./components/EbookReaderModeComponent.vue').default);
Vue.component('vocabulary-practice-component', require('./components/VocabularyPracticeComponent.vue').default);
Vue.component('edit-flash-card-collection-component', require('./components/EditFlashCardCollectionComponent.vue').default);
Vue.component('flash-card-practice-component', require('./components/FlashCardPracticeComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});


