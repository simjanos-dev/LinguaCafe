from django.contrib import admin
from django.urls import path

from . import views 

urlpatterns = [
    path('', views.tokenizer, name='tokenizer'),
    path('import', views.importBook, name='import-book'),
]
