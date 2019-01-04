#!/bin/sh
export OUT_PATH='output'
rm -fr $OUT_PATH
mkdir $OUT_PATH
cp -r conf data demo log src tests composer.json readme.txt $OUT_PATH
zip -r ecloudsdk.zip output/*
rm -fr $OUT_PATH/*
mv ecloudsdk.zip $OUT_PATH/
