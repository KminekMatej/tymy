@echo off
IF [%1] == [] (
	echo "GIT project directory not specified. Assuming current directory: %~dp0"
	set dir=%~dp0
) ELSE (
	echo "GIT project dDirectory specified: %~dp1"
	set dir=%~dp1
)

cd %dir%
echo Getting last tag name as version
git describe --abbrev=0 --tags > ./app/config/version.tmp
set /p ver=<./app/config/version.tmp	
del .\app\config\version.tmp
echo Current version is %ver%

for /f "tokens=1,2,3 delims=." %%a in ("%ver%") do set major=%%a&set minor=%%b&set patch=%%c
set /a "newmajor=%major%+2"
set "newver=%newmajor%.0.0"
echo New version with increased patch will be %newver%

:Ask
echo Would you like to increase version to %newver%?(y/n)
set INPUT=
set /P INPUT=Type input: %=%
If /I "%INPUT%"=="y" goto yes 
If /I "%INPUT%"=="n" goto no
echo Incorrect input & goto Ask
:yes
echo Increasing patch number to %newver%
git tag -a %newver% -m "New patch released, marked as %newver%"
git push github %newver%
:no
rem Now write all tags to tag file
git tag -l --format="%%(taggerdate:iso8601)|%%(refname:short)" --sort=-v:refname > %dir%/app/tag.log
echo Done.
:End
Pause&Exit