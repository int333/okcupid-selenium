'use strict';
var scripts = document.getElementsByTagName("script"),
    currentScriptUrl = (document.currentScript || scripts[scripts.length - 1]).src,
    currentScriptUrlParts = currentScriptUrl.replace(location.origin + '/', '').split('/');
currentScriptUrlParts.pop();
window.AppDir = currentScriptUrlParts.join('/') + '/';



var drLevApp = angular.module('drLevApp', ['phoneList']);
