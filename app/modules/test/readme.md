Tester for Tymy.CZ API project

Tests are run using bash script tester.sh directly from shell.
Script contains neccessary basic configuration to run all tests.
Without path, all of the tests are run (if they are not marked as @skip)
You can add any path you wish to run tests.

Root directory of all module test is ./app

Tests must be separated by modules to their own folders.
Tests run in 4 parallel processes. Can be expanded to 8, but that can get pretty memory consuming

When testing on local, file config.test.local.neon must be in the root directory, containing all the neccessary variables. These variables holds important record IDs, used for testing.
When trying to run test on localhost, you have to create an empty database, migrate it to latest state and then run data.sql file, which imports important records to its tables, so IDs are fixed according to config.test.local.neon file.

Examples of usage:

bash tester.sh                ... Runs all tests
bash tester.sh app/event          ... Runs all event tests
