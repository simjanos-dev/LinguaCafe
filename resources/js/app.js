/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import axios from 'axios'
import Vue from 'vue';
import VueRouter from 'vue-router';
import vuetify from './vuetify';

require('./bootstrap');
window.Vue = require('vue').default;
var VueCookie = require('vue-cookie');
window.Vue.use(VueCookie);
window.Vue.use(VueRouter);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


import VueSlider from 'vue-slider-component';
import LayoutComponent from './components/LayoutComponent.vue';
import GoalComponent from './components/GoalComponent.vue';
Vue.component('vue-slider', VueSlider);
Vue.component('layout-component', LayoutComponent);
Vue.component('goal-component', GoalComponent);





// dialogs
import StartReviewDialog from './components/dialogs/StartReviewDialog';
import ThemeSelectionDialog from './components/dialogs/ThemeSelectionDialog';
import LanguageSelectionDialog from './components/dialogs/LanguageSelectionDialog';
Vue.component('start-review-dialog', StartReviewDialog);
Vue.component('theme-selection-dialog', ThemeSelectionDialog);
Vue.component('language-selection-dialog', LanguageSelectionDialog);


const HomeComponent = require('./components/HomeComponent.vue').default;
const BookListComponent = require('./components/BookListComponent.vue').default;
const CreateBookComponent = require('./components/CreateBookComponent.vue').default;
const ChapterListComponent = require('./components/ChapterListComponent.vue').default;
const EditChapterComponent = require('./components/EditChapterComponent.vue').default;
const ReaderComponent = require('./components/ReaderComponent.vue').default;
const FlashcardCollectionListComponent = require('./components/FlashcardCollectionListComponent.vue').default;
const FlashcardCollectionComponent = require('./components/FlashcardCollectionComponent.vue').default;
const ReviewComponent = require('./components/ReviewComponent.vue').default;
const VocabularyComponent = require('./components/VocabularyComponent.vue').default;
const KanjiListComponent = require('./components/KanjiListComponent.vue').default;
const KanjiDetailsComponent = require('./components/KanjiDetailsComponent.vue').default;

const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/', component: HomeComponent },
        { path: '/books', component: BookListComponent },
        { path: '/books/create', component: CreateBookComponent },
        { path: '/chapters/:bookId', component: ChapterListComponent },
        { path: '/chapters/read/:chapterId', component: ReaderComponent },
        { path: '/chapters/create/:bookId', component: EditChapterComponent },
        { path: '/chapters/edit/:bookId/:chapterId', component: EditChapterComponent },
        { path: '/flashcards', component: FlashcardCollectionListComponent },
        { path: '/flashcards/edit/:flashcardCollectionId?', component: FlashcardCollectionComponent },
        { path: '/review/:practiceMode?/:bookId?/:chapterId?', component: ReviewComponent },
        { path: '/vocabulary/search', component: VocabularyComponent },
        { path: '/vocabulary/search/:text/:stage/:book/:chapter/:translation/:phrases/:orderBy/:page', component: VocabularyComponent },
        { path: '/kanji/search', component: KanjiListComponent },
        { path: '/kanji/:character', component: KanjiDetailsComponent },
    ]
})

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    router,
    el: '#app',
    vuetify
});


