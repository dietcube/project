#!/bin/bash
set -e

usage() {
    echo "USAGE: `basename $0` [Options]"
    echo ""
    echo "Required:"
    echo ""
    echo "Options:"
    echo "  -h, --help          show this help."
    echo "  -v, --verbose       show detail commands."
    echo "  --dry               dry run mode."
    exit 1;
}

main() {
    script_dir=$(cd $(dirname $0); pwd)
    opts=`getopt -o hv: -l help,verbose:,dry, -- "$@"`
    eval set -- "$opts"
    while [ -n "$1" ]; do
        case $1 in
            -h|--help) usage;;
            -v|--verbose) is_verbose=1;;
            --dry) is_dry=1;;
            --) shift; break;;
            *) usage;;
        esac
        shift
    done

    current_namespace=$(echo $(basename $(pwd)) | sed -r 's/(^|-)(.)/\U\2\E/g')

    if [ $is_dry ];then
        info "dry run..."
    fi

    run echo "Initialize $current_namespace ..."
    run sed -i -e "s/SampleApp/${current_namespace}/g" composer.json
    run find app -name *.php | xargs sed -i -e "s/SampleApp/${current_namespace}/g"
    run find app -name *.html.twig | xargs sed -i -e "s/SampleApp/${current_namespace}/g"
    run find tests -name *.php | xargs sed -i -e "s/SampleApp/${current_namespace}/g"
    run sed -i -e "s/SampleApp/${current_namespace}/g" webroot/index.php
    run mv app/config/config_development.php.sample app/config/config_development.php
    run chmod 777 tmp
    run composer dumpautoload
    run echo "-------------------------------------------------------------------------"
    run echo ""
    run echo "Dietcube setup completed."
    run echo ""
    run echo "Try now with built-in server:"
    run echo "$ DIET_ENV=development php -d variables_order=EGPCS -S 0:8999 -t webroot/"
    run echo ""
    run echo "-------------------------------------------------------------------------"
}

## utility
run() {
    if [ $is_dry ]; then
        echo "[dry run] $@"
    else
        if [ $is_verbose ];then
            echo "[run] $@"
        fi
        eval "$@"
    fi
}

red() {
    echo -n "[1;31m$1[0m"
}

yellow() {
    echo -n "[1;33m$1[0m"
}

green() {
    echo -n "[1;32m$1[0m"
}

fatal() {
    red "[fatal] "
    echo "$1"
}

warn() {
    yellow "[warn] "
    echo "$1"
}

info() {
    green "[info] "
    echo "$1"
}

# call main.
main "$@"

