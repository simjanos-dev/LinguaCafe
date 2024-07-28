import Vue from 'vue';
import VueRouter from 'vue-router';
import vuetify from './vuetify';

require('./bootstrap');
window.Vue = require('vue').default;
var VueCookie = require('vue-cookie');
window.Vue.use(VueCookie);
window.Vue.use(VueRouter);


// vue showdown
import VueShowdown from 'vue-showdown'

Vue.use(VueShowdown, {
    // set default flavor of showdown
    flavor: 'github',
    // set default options of showdown (will override the flavor options)
    options: {
      emoji: false,
    },
  })


// layout
import Layout from './components/Layout.vue';
Vue.component('layout', Layout);

// library
import Book from './components/Library/Book.vue';
import BookListTable from './components/Library/BookListLayout/BookListTable.vue';
import BookListDetailed from './components/Library/BookListLayout/BookListDetailed.vue';
import BookListCoverOnly from './components/Library/BookListLayout/BookListCoverOnly.vue';
import EditBookDialog from './components/Library/EditBookDialog.vue';
import BookChapters from './components/Library/BookChapters.vue';
import EditBookChapterDialog from './components/Library/EditBookChapterDialog.vue';
import DeleteBookChapterDialog from './components/Library/DeleteBookChapterDialog.vue';
import DeleteBookDialog from './components/Library/DeleteBookDialog.vue';
Vue.component('book', Book);
Vue.component('book-list-table', BookListTable);
Vue.component('book-list-detailed', BookListDetailed);
Vue.component('book-list-cover-only', BookListCoverOnly);
Vue.component('edit-book-dialog', EditBookDialog);
Vue.component('book-chapters', BookChapters);
Vue.component('edit-book-chapter-dialog', EditBookChapterDialog);
Vue.component('delete-book-chapter-dialog', DeleteBookChapterDialog);
Vue.component('delete-book-dialog', DeleteBookDialog);

// library import
import ImportDialog from './components/Library/Import/ImportDialog.vue';
import ImportTypeSelection from './components/Library/Import/ImportTypeSelection.vue';
import ImportPlainTextSource from './components/Library/Import/ImportSource/ImportPlainTextSource.vue';
import ImportTextFileSource from './components/Library/Import/ImportSource/ImportTextFileSource.vue';
import ImportSubtitleFileSource from './components/Library/Import/ImportSource/ImportSubtitleFileSource.vue';
import ImportEbookFileSource from './components/Library/Import/ImportSource/ImportEbookFileSource.vue';
import ImportYoutubeSubtitleSource from './components/Library/Import/ImportSource/ImportYoutubeSubtitleSource.vue';
import ImportJellyfinSubtitleSource from './components/Library/Import/ImportSource/ImportJellyfinSubtitleSource.vue';
import ImportWebsiteSource from './components/Library/Import/ImportSource/ImportWebsiteSource.vue';
import ImportLibraryOptions from './components/Library/Import/ImportLibraryOptions.vue';
import ImportOptions from './components/Library/Import/ImportOptions.vue';
Vue.component('import-dialog', ImportDialog);
Vue.component('import-type-selection', ImportTypeSelection);
Vue.component('import-plain-text-source', ImportPlainTextSource);
Vue.component('import-text-file-source', ImportTextFileSource);
Vue.component('import-subtitle-file-source', ImportSubtitleFileSource);
Vue.component('import-ebook-file-source', ImportEbookFileSource);
Vue.component('import-youtube-subtitle-source', ImportYoutubeSubtitleSource);
Vue.component('import-jellyfin-subtitle-source', ImportJellyfinSubtitleSource);
Vue.component('import-website-source', ImportWebsiteSource);
Vue.component('import-library-options', ImportLibraryOptions);
Vue.component('import-options', ImportOptions);

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
import TextBlockGroup from './components/Text/TextBlockGroup.vue';
import VocabularyBox from './components/Text/VocabularyBox.vue';
import VocabularyBottomSheet from './components/Text/VocabularyBottomSheet.vue';
import VocabularyHoverBox from './components/Text/VocabularyHoverBox.vue';
import VocabularySideBox from './components/Text/VocabularySideBox.vue';
import VocabularySearchBox from './components/Text/VocabularySearchBox.vue';
import DeletePhraseDialog from './components/Text/DeletePhraseDialog.vue';
Vue.component('text-block-group', TextBlockGroup);
Vue.component('vocabulary-box', VocabularyBox);
Vue.component('vocabulary-bottom-sheet', VocabularyBottomSheet);
Vue.component('vocabulary-hover-box', VocabularyHoverBox);
Vue.component('vocabulary-side-box', VocabularySideBox);
Vue.component('vocabulary-search-box', VocabularySearchBox);
Vue.component('delete-phrase-dialog', DeletePhraseDialog);

// text reader
import TextReaderSettings from './components/TextReader/TextReaderSettings.vue';
import TextReaderGlossary from './components/TextReader/TextReaderGlossary.vue';
import TextReaderChapterList from './components/TextReader/TextReaderChapterList.vue';
import TextReaderHotkeyInformationDialog from './components/TextReader/TextReaderHotkeyInformationDialog';
Vue.component('text-reader-hotkey-information-dialog', TextReaderHotkeyInformationDialog);
Vue.component('text-reader-settings', TextReaderSettings);
Vue.component('text-reader-glossary', TextReaderGlossary);
Vue.component('text-reader-chapter-list', TextReaderChapterList);

// Jellyfin
import JellyfinSubtitleList from './components/Library/Import/ImportSource/JellyfinSubtitleList';
Vue.component('jellyfin-subtitle-list', JellyfinSubtitleList);

// vocabulary
import VocabularyEditDialog from './components/Vocabulary/VocabularyEditDialog';
import VocabularyExportDialog from './components/Vocabulary/VocabularyExportDialog';
import VocabularyImportDialog from './components/Vocabulary/VocabularyImportDialog';
Vue.component('vocabulary-edit-dialog', VocabularyEditDialog);
Vue.component('vocabulary-export-dialog', VocabularyExportDialog);
Vue.component('vocabulary-import-dialog', VocabularyImportDialog);

// review
import ReviewHotkeyInformationDialog from './components/Review/ReviewHotkeyInformationDialog';
import ReviewSettings from './components/Review/ReviewSettings';
Vue.component('review-hotkey-information-dialog', ReviewHotkeyInformationDialog);
Vue.component('review-settings', ReviewSettings);

// dialogs
import LogoutDialog from './components/Dialogs/LogoutDialog';
import ErrorDialog from './components/Dialogs/ErrorDialog';
import StartReviewDialog from './components/Dialogs/StartReviewDialog';
import ThemeSelectionDialog from './components/Dialogs/ThemeSelectionDialog';
import LanguageSelectionDialog from './components/Dialogs/LanguageSelectionDialog';
Vue.component('logout-dialog', LogoutDialog);
Vue.component('error-dialog', ErrorDialog);
Vue.component('start-review-dialog', StartReviewDialog);
Vue.component('theme-selection-dialog', ThemeSelectionDialog);
Vue.component('language-selection-dialog', LanguageSelectionDialog);

// user settings
import UserSettingsThemes from './components/UserSettings/UserSettingsThemes';
import ChangePasswordDialog from './components/UserSettings/ChangePasswordDialog';
Vue.component('change-password-dialog', ChangePasswordDialog);
Vue.component('user-settings-themes', UserSettingsThemes);

// admin settings
import AdminUserSettings from './components/Admin/AdminUserSettings';
import AdminFontTypeSettings from './components/Admin/AdminFontTypeSettings';
import AdminEditFontTypeDialog from './components/Admin/AdminEditFontTypeDialog';
import AdminDeleteFontTypeDialog from './components/Admin/AdminDeleteFontTypeDialog';
import AdminLanguageSettings from './components/Admin/AdminLanguageSettings';
import AdminInstallLanguageDialog from './components/Admin/AdminInstallLanguageDialog';
import AdminUninstallLanguagesDialog from './components/Admin/AdminUninstallLanguagesDialog';
import AdminDictionarySettings from './components/Admin/AdminDictionarySettings';
import AdminDeleteDictionaryDialog from './components/Admin/AdminDeleteDictionaryDialog';
import AdminEditDictionaryDialog from './components/Admin/AdminEditDictionaryDialog';
import AdminApiSettings from './components/Admin/AdminApiSettings';
import AdminDictionaryImportDialog from './components/Admin/AdminDictionaryImportDialog';
import AdminExternalDictionaryImport from './components/Admin/AdminExternalDictionaryImport';
import AdminSupportedDictionaryImport from './components/Admin/AdminSupportedDictionaryImport';
import AdminDeeplDictionaryCreation from './components/Admin/AdminDeeplDictionaryCreation';
import AdminMyMemoryDictionaryCreation from './components/Admin/AdminMyMemoryDictionaryCreation';
import AdminEditUserDialog from './components/Admin/AdminEditUserDialog';
import AdminReviewSettings from './components/Admin/AdminReviewSettings';
Vue.component('admin-user-settings', AdminUserSettings);
Vue.component('admin-language-settings', AdminLanguageSettings);
Vue.component('admin-font-type-settings', AdminFontTypeSettings);
Vue.component('admin-edit-font-type-dialog', AdminEditFontTypeDialog);
Vue.component('admin-delete-font-type-dialog', AdminDeleteFontTypeDialog);
Vue.component('admin-install-language-dialog', AdminInstallLanguageDialog);
Vue.component('admin-uninstall-languages-dialog', AdminUninstallLanguagesDialog);
Vue.component('admin-dictionary-settings', AdminDictionarySettings);
Vue.component('admin-delete-dictionary-dialog', AdminDeleteDictionaryDialog);
Vue.component('admin-edit-dictionary-dialog', AdminEditDictionaryDialog);
Vue.component('admin-api-settings', AdminApiSettings);
Vue.component('admin-dictionary-import-dialog', AdminDictionaryImportDialog);
Vue.component('admin-external-dictionary-import', AdminExternalDictionaryImport);
Vue.component('admin-supported-dictionary-import', AdminSupportedDictionaryImport);
Vue.component('admin-deepl-dictionary-creation', AdminDeeplDictionaryCreation);
Vue.component('admin-my-memory-dictionary-creation', AdminMyMemoryDictionaryCreation);
Vue.component('admin-edit-user-dialog', AdminEditUserDialog);
Vue.component('admin-review-settings', AdminReviewSettings);


// user manual
const UserManual = require('./components/UserManual/UserManual.vue').default;
import UserManualIntroduction from './components/UserManual/Pages/UserManualIntroduction';
import UserManualBackup from './components/UserManual/Pages/UserManualBackup';
import UserManualLanguages from './components/UserManual/Pages/UserManualLanguages';
import UserManualReading from './components/UserManual/Pages/UserManualReading';
import UserManualVocabularyImport from './components/UserManual/Pages/UserManualVocabularyImport';

Vue.component('user-manual-introduction', UserManualIntroduction);
Vue.component('user-manual-backup', UserManualBackup);
Vue.component('user-manual-languages', UserManualLanguages);
Vue.component('user-manual-reading', UserManualReading);
Vue.component('user-manual-vocabulary-import', UserManualVocabularyImport);

const DevelopmentTools = require('./components/DevelopmentTools.vue').default;
const LoginForm = require('./components/Login/LoginForm.vue').default;
const UserSettingsLayout = require('./components/UserSettings/UserSettingsLayout.vue').default;
const AdminSettingsLayout = require('./components/Admin/AdminSettingsLayout.vue').default;
const Home = require('./components/Home/Home.vue').default;
const PatchNotes = require('./components/Home/PatchNotes.vue').default;
const Attributions = require('./components/Home/Attributions.vue').default;
const Library = require('./components/Library/Library.vue').default;
const TextReader = require('./components/TextReader/TextReader.vue').default;
const Review = require('./components/Review/Review.vue').default;
const Vocabulary = require('./components/Vocabulary/Vocabulary.vue').default;
const KanjiList = require('./components/Kanji/KanjiList.vue').default;
const KanjiDetails = require('./components/Kanji/KanjiDetails.vue').default;
Vue.component('attributions', Attributions);

const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/dev', component: DevelopmentTools },
        { path: '/', component: Home },
        { path: '/user-settings', component: UserSettingsLayout },
        { path: '/admin/:page?', component: AdminSettingsLayout },
        { path: '/user-manual/:curre0ntPage?', component: UserManual },
        { path: '/patch-notes', component: PatchNotes },
        { path: '/attributions', component: Attributions },
        { path: '/login', component: LoginForm },
        { path: '/books/:bookId?', component: Library },
        { path: '/chapters/read/:chapterId', component: TextReader },
        { path: '/review/:practiceMode?/:bookId?/:chapterId?', component: Review },
        { path: '/vocabulary/search', component: Vocabulary },
        { path: '/vocabulary/search/:text/:stage/:book/:chapter/:translation/:phrases/:orderBy/:page', component: Vocabulary },
        { path: '/kanji/search', component: KanjiList },
        { path: '/kanji/:character', component: KanjiDetails },
    ]
})

// vuex
import Vuex from 'vuex'
import SharedStore from './vuex/Shared.js';
import InteractiveTextStore from './vuex/InteractiveText.js';
import HoverVocabularyBoxStore from './vuex/HoverVocabularyBox.js';
import VocabularyBoxStore from './vuex/VocabularyBox.js';

Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        shared: SharedStore,
        interactiveText: InteractiveTextStore,
        hoverVocabularyBox: HoverVocabularyBoxStore,
        vocabularyBox: VocabularyBoxStore,
    }
});

Vue.use(store);
const app = new Vue({
    router,
    el: '#app',
    vuetify,
    store,
});


