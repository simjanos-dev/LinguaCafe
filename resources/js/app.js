import Vue from 'vue';
import VueRouter from 'vue-router';
import vuetify from './vuetify';

require('./bootstrap');
window.Vue = require('vue').default;
var VueCookie = require('vue-cookie');
window.Vue.use(VueCookie);
window.Vue.use(VueRouter);

// layout
import Layout from './components/Layout.vue';
Vue.component('layout', Layout);

// library
import EditBookDialog from './components/Library/EditBookDialog.vue';
import BookChapters from './components/Library/BookChapters.vue';
import EditBookChapterDialog from './components/Library/EditBookChapterDialog.vue';
import DeleteBookChapterDialog from './components/Library/DeleteBookChapterDialog.vue';
import DeleteBookDialog from './components/Library/DeleteBookDialog.vue';
Vue.component('edit-book-dialog', EditBookDialog);
Vue.component('book-chapters', BookChapters);
Vue.component('edit-book-chapter-dialog', EditBookChapterDialog);
Vue.component('delete-book-chapter-dialog', DeleteBookChapterDialog);
Vue.component('delete-book-dialog', DeleteBookDialog);

// home page
import Calendar from './components/Home/Calendar.vue';
import Goals from './components/Home/Goals.vue';
import Goal from './components/Home/Goal.vue';
import EditGoalDialog from './components/Home/EditGoalDialog.vue';
import Statistics from './components/Home/Statistics.vue';
Vue.component('calendar', Calendar);
Vue.component('goals', Goals);
Vue.component('goal', Goal);
Vue.component('edit-goal-dialog', EditGoalDialog);
Vue.component('statistics', Statistics);

// text 
import TextBlock from './components/Text/TextBlock.vue';
import TextBlockGroup from './components/Text/TextBlockGroup.vue';
Vue.component('text-block', TextBlock);
Vue.component('text-block-group', TextBlockGroup);

// text reader
import TextReaderSettings from './components/TextReader/TextReaderSettings.vue';
import TextReaderGlossary from './components/TextReader/TextReaderGlossary.vue';
import TextReaderChapterList from './components/TextReader/TextReaderChapterList.vue';
Vue.component('text-reader-settings', TextReaderSettings);
Vue.component('text-reader-glossary', TextReaderGlossary);
Vue.component('text-reader-chapter-list', TextReaderChapterList);

// media player
const SubtitleReader = require('./components/MediaPlayer/SubtitleReader.vue').default;
const SubtitleList = require('./components/MediaPlayer/SubtitleList.vue').default;
const SubtitleReaderSettings = require('./components/MediaPlayer/SubtitleReaderSettings.vue').default;
Vue.component('subtitle-reader', SubtitleReader);
Vue.component('subtitle-reader-settings', SubtitleReaderSettings);
Vue.component('subtitle-list', SubtitleList);

// vocabulary
import VocabularyEditDialog from './components/Vocabulary/VocabularyEditDialog';
import VocabularyExportDialog from './components/Vocabulary/VocabularyExportDialog';
Vue.component('vocabulary-edit-dialog', VocabularyEditDialog);
Vue.component('vocabulary-export-dialog', VocabularyExportDialog);

// dialogs
import ErrorDialog from './components/Dialogs/ErrorDialog';
import StartReviewDialog from './components/Dialogs/StartReviewDialog';
import ThemeSelectionDialog from './components/Dialogs/ThemeSelectionDialog';
import LanguageSelectionDialog from './components/Dialogs/LanguageSelectionDialog';
Vue.component('error-dialog', ErrorDialog);
Vue.component('start-review-dialog', StartReviewDialog);
Vue.component('theme-selection-dialog', ThemeSelectionDialog);
Vue.component('language-selection-dialog', LanguageSelectionDialog);

// user settings
import ChangePasswordDialog from './components/UserSettings/ChangePasswordDialog';
Vue.component('change-password-dialog', ChangePasswordDialog);

// admin
import AdminUserSettings from './components/Admin/AdminUserSettings';
import AdminDictionarySettings from './components/Admin/AdminDictionarySettings';
import AdminApiSettings from './components/Admin/AdminApiSettings';
import AdminDeleteDictionaryDialog from './components/Admin/AdminDeleteDictionaryDialog';
import AdminDictionaryImportDialog from './components/Admin/AdminDictionaryImportDialog';
import AdminEditUserDialog from './components/Admin/AdminEditUserDialog';
import AdminReviewSettings from './components/Admin/AdminReviewSettings';
Vue.component('admin-user-settings', AdminUserSettings);
Vue.component('admin-dictionary-settings', AdminDictionarySettings);
Vue.component('admin-api-settings', AdminApiSettings);
Vue.component('admin-delete-dictionary-dialog', AdminDeleteDictionaryDialog);
Vue.component('admin-dictionary-import-dialog', AdminDictionaryImportDialog);
Vue.component('admin-edit-user-dialog', AdminEditUserDialog);
Vue.component('admin-review-settings', AdminReviewSettings);

const LoginForm = require('./components/Login/LoginForm.vue').default;
const AdminLayout = require('./components/Admin/AdminLayout.vue').default;
const MediaPlayer = require('./components/MediaPlayer/MediaPlayer.vue').default;
const Home = require('./components/Home/Home.vue').default;
const Books = require('./components/Library/Books.vue').default;
const TextReader = require('./components/TextReader/TextReader.vue').default;
const FlashcardCollectionList = require('./components/FlashcardCollectionList.vue').default;
const FlashcardCollection = require('./components/FlashcardCollection.vue').default;
const Review = require('./components/Review.vue').default;
const Vocabulary = require('./components/Vocabulary/Vocabulary.vue').default;
const KanjiList = require('./components/KanjiList.vue').default;
const KanjiDetails = require('./components/KanjiDetails.vue').default;


const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/', component: Home },
        { path: '/login', component: LoginForm },
        { path: '/admin', component: AdminLayout },
        { path: '/media-player', component: MediaPlayer },
        { path: '/books', component: Books },
        { path: '/chapters/read/:chapterId', component: TextReader },
        { path: '/flashcards', component: FlashcardCollectionList },
        { path: '/flashcards/edit/:flashcardCollectionId?', component: FlashcardCollection },
        { path: '/review/:practiceMode?/:bookId?/:chapterId?', component: Review },
        { path: '/vocabulary/search', component: Vocabulary },
        { path: '/vocabulary/search/:text/:stage/:book/:chapter/:translation/:phrases/:orderBy/:page', component: Vocabulary },
        { path: '/kanji/search', component: KanjiList },
        { path: '/kanji/:character', component: KanjiDetails },
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


