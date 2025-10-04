#!/bin/bash

# Vind testzadel routes in web.php
grep -n -A5 -B5 "testzadel\|TestzadelController" routes/web.php