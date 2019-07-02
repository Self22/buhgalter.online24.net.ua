@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/google/cloud/Core/bin/google-cloud-batch
php "%BIN_TARGET%" %*
