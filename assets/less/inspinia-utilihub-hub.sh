#! /bin/bash

lessc ./inspinia-utilihub-hub/style.less ../css/utilihub-hub.css
lessc --clean-css ../css/utilihub-hub.css ../css/utilihub-hub.min.css
