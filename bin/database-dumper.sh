#!/usr/bin/env bash
# IMPORTANT! On linux, if this doesn't work by default (should not) here is how you make it executable
# [root@host]# cd ...path to this file directory
# [root@host]# sudo chmod 755 database-dumper.sh

# IMPORTANT! In case you still get errors, make sure line ending is converted from Windows to Linux
# [root@host]# awk '{ sub("\r$", ""); print }' database-dumper.sh > _database-dumper.sh
# [root@host]# rm database-dumper.sh
# [root@host]# mv _database-dumper.sh database-dumper.sh
# Alternatively you can use dos2unix
# [root@host]# dos2unix database-dumper.sh

# Here is a good zip utility for Windows
# http://stahlworks.com/dev/?tool=zipunzip

function _help {
echo "Options:"
echo -e "\t--help"
echo -e "\t-d [DB_NAME, database name, required]"
echo -e "\t-u [DB_USERNAME, database username, optional]"
echo -e "\t-p [DB_PASSWORD, database password, required]"
echo -e "\t-e [EXPORT_AS, export as type, required, string, options(csv, sql)]"
echo -e "\t--dist [DIST_DIR, destination directory, required]"
echo -e "\t--tmp [TMP_DIR, temporary directory, required]"
echo -e "\t--name [ARCHIVE_NAME, export as file name, required]"
echo -e "\t--prefixes [ALLOWED_TABLE_PREFIXES, allowed table prefixes, required, pipe, example(us_|ld|l_i)]"
echo -e "\nUsage:"
echo -e "\tbin/database-dumper.sh -d some_database -u root -p -e csv --dist path/dist/ --tmp path/temp/ --name some_name --prefixes 'ai_|as_'"
echo -e "\tbin/database-dumper.sh --help"
}

# read the options
TEMP=`getopt -o d:u:p::e: --long help,dist:,tmp:,name:,prefixes: -- "$@"`
eval set -- "${TEMP}"

# extract options and their arguments into variables.
# http://www.bahmanm.com/blogs/command-line-options-how-to-parse-in-bash-using-getopt
while true ; do
    case "$1" in
        --help) _help; exit 1;;
        -d)
            case "$2" in
                "") shift 2 ;;
                *) DB_NAME=${2}; shift 2;;
            esac ;;
        -u)
            case "$2" in
                "") shift 2 ;;
                *) DB_USERNAME=${2}; shift 2;;
            esac ;;
        -p)
            case "$2" in
                "") DB_PASSWORD=''; shift 2;;
                *) DB_PASSWORD=$2; shift 2;;
            esac ;;
        -e)
            case "$2" in
                "") shift 2 ;;
                *) EXPORT_AS=${2}; shift 2;;
            esac ;;
        --dist)
            case "$2" in
                "") shift 2 ;;
                *) DIST_DIR=${2}; shift 2;;
            esac ;;
        --tmp)
            case "$2" in
                "") shift 2 ;;
                *) TMP_DIR=${2}; shift 2;;
            esac ;;
        --name)
            case "$2" in
                "") shift 2 ;;
                *) ARCHIVE_NAME=${2}; shift 2;;
            esac ;;
        --prefixes)
            case "$2" in
                "") shift 2 ;;
                *) ALLOWED_TABLE_PREFIXES=${2}; shift 2;;
            esac ;;
        --) shift ; break ;;
        *) echo "Internal error!" ; exit 1 ;;
    esac
done

# http://misc.flogisoft.com/bash/tip_colors_and_formatting
for OPTION in 'DB_NAME' 'DB_USERNAME' 'EXPORT_AS' 'DIST_DIR' 'TMP_DIR' 'ARCHIVE_NAME' 'ALLOWED_TABLE_PREFIXES'; do
    if [ -z "${!OPTION}" ]; then
        echo -e "\e[35mMissing option ${OPTION}\e[0m"
        _help
        exit;
    fi
done

# Create necessary directories
if [ ! -d "${DIST_DIR}" ]; then
    mkdir -p ${DIST_DIR}
fi
if [ ! -d "${TMP_DIR}" ]; then
    mkdir -p ${TMP_DIR}
fi

# http://www.canbike.org/information-technology/sed-delete-carriage-returns-and-linefeeds-crlf.html
# http://stackoverflow.com/questions/18599711/how-can-i-split-a-shell-command-over-multiple-lines-when-using-an-if-statement
function dumpToCSV {
    TABLE=$(echo "${TABLE}"|sed "s/\r//g;s/\n//g")
    mysql -B -u ${DB_USERNAME} -p"${DB_PASSWORD}" ${DB_NAME} -h localhost -e \
    "SELECT * FROM ${TABLE};" \
    |sed "s/'/\'/g;s/\"/”/g;s/\t/\",\"/g;s/^/\"/;s/$/\"/;s/\n//g;s/\r//g" \
    > ${TMP_DIR}${TABLE}".csv"
}

if [ ${EXPORT_AS} == "csv" ]; then
    # Get all tables from the database
    for TABLE in $(mysql -u ${DB_USERNAME} -p"${DB_PASSWORD}" ${DB_NAME} -sN -e "SHOW TABLES;"); do
        # Save table to CSV file (only if allowed)
        if [ "${ALLOWED_TABLE_PREFIXES}" == "all" ]; then
            dumpToCSV
        else
            IFS='|' read -ra ADDR <<< "${ALLOWED_TABLE_PREFIXES}"
            for i in "${ADDR[@]}"; do
                if [ "${i}" == "${TABLE:0:${#i}}" ]; then
                    dumpToCSV
                fi
            done
        fi
    done

    # Add all CSV files to a zip archive
    # tar -czvf ${DIST_DIR}${ARCHIVE_NAME} ${TMP_DIR}*
    zip -rj "${DIST_DIR}${ARCHIVE_NAME}.zip" ${TMP_DIR}*

    # Remove TMP directory
    rm -r ${TMP_DIR}
fi

if [ ${EXPORT_AS} == "sql" ]; then
    mysqldump -u ${DB_USERNAME} -p"${DB_PASSWORD}" ${DB_NAME} | gzip -c > "${DIST_DIR}${ARCHIVE_NAME}.gz"
fi
