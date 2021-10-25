#!/bin/bash -v

function Main
{
    RUN_DIR="$( cd "$(dirname "$0")" ; pwd -P )"
    DATETIME=`date '+%d.%m.%Y %H:%M:%S'`
    MIGRATION=`date '+%Y-%m-%dT%H-%M-%S'`
    USERNAME=`git config user.name`
    EMAIL=`git config user.email`
    NEWFILE="$MIGRATION.sql"
    AUTHOR="$USERNAME <$EMAIL>"

    cp "$RUN_DIR/_template.sql" "$RUN_DIR/$NEWFILE"

    sed -i "s/_DATETIME_/$DATETIME/g" "$RUN_DIR/$NEWFILE"
    sed -i "s/_MIGRATION_/$MIGRATION/g" "$RUN_DIR/$NEWFILE"
    sed -i "s/_AUTHOR_/$AUTHOR/g" "$RUN_DIR/$NEWFILE"
    sed -i "s/_NEWFILE_/$NEWFILE/g" "$RUN_DIR/$NEWFILE"
}

Main $@
