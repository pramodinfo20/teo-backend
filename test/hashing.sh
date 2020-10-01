#!/bin/bash
LANG = en_DK.iso885915  #de_DE #de_DE.ISO-8859-15 #de_DE.utf8
export LANG

filename=$1
IFS=$';\r'   #; is separator \r is line end (\cr\lf)
#i=0
hashvalue=''
myoutput="$1"'_hashed'
MIFS=';' # for separator for output file

#write new header
echo "Bereich;KST;Beschreibung;hash;Status" > "$myoutput"

{
read #read header nad do nothing with it
while read Bereich KST Beschreibung Nachname Vorname Status
do
    #ignore header

    name="$Vorname"'.'"$Nachname"
    echo $name
    hashvalue=$(sha256sum <<< "$name"  | cut -f 1 -d ' ' )
    #echo $hashvalue

    line="$Bereich""$MIFS""$KST""$MIFS""$Beschreibung""$MIFS""$hashvalue""$MIFS""$Status"
    #echo $line
    echo "$line" >> "$myoutput"

    #if [ "$i" -gt 3 ]
    #then
    #    break
    #fi
    #((i++))
done
} < $filename
