from django.contrib import admin
from django.urls import path

from . import views 

urlpatterns = [
    path('', views.tokenizer, name='tokenizer'),
    path('import-book', views.importBook, name='import-book'),
    path('import-text', views.importText, name='import-text'),
    path('get-youtube-subtitle-list', views.getYoutubeSubtitles, name='get-youtube-subtitle-list'),
]
