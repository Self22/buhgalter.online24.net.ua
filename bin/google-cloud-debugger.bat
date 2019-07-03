@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/google/cloud/Debugger/bin/google-cloud-debugger
php "%BIN_TARGET%" %*
