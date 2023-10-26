# JSTOR plant specimens

JSTOR’s [Global Plants](https://plants.jstor.org) includes a database of plant specimens, many of which are types. While many of these records are in GBIF, many aren’t. The code in this repository scrapes JSTOR to build a list of the specimens it has. The goal is to (a) have a list of what JSTOR has, (b) map those records to the equivalent records in GBIF to discover gaps in GBIF coverage, and (c) make it easier to link to JSTOR plant specimens. 

JSTOR uses a DOI-like identifier, e.g. [10.5555/al.ap.specimen.bm000753002](https://plants.jstor.org/stable/10.5555/al.ap.specimen.bm000753002), where `bm000753002` is a combination of herbarium code (see [Global Plants Partners](https://plants.jstor.org/partners)) and the specimen barcode (barcode in this context is a literal barcode, typically visible on the herbarium sheet).

## Challenges

In addition to identifier issues (barcodes not used, related to secondary identifiers, only found on images, etc.) there is the problem of taxonomy. Specimens may be stored under the current name of the species, not the name for which the specimen is a type. For example,  `LL00373047` is a type for “Beloperone sanmartensis” but in GBIF this record is https://www.gbif.org/occurrence/4110152991 and stored under “Justicia rohrii”. There is no mention of this name in the GBIF metadata, but the image of the specimen clearly shows “Beloperone sanmartensis”.http://was.tacc.utexas.edu/fileget?coll=TEX-LL&type=O&filename=sp62952952590483191168.att.jpg

## OccurrenceIDs

These are a hot mess of URLs, URNs, UUIDs, integers etc.

### MBM

OccurrenceID is an integer, but the bibliographic citation field  contains a URL that uses it, e.g. http://herbariovirtualreflora.jbrj.gov.br/reflora/herbarioVirtual/ConsultaPublicoHVUC/ConsultaPublicoHVUC.do?idTestemunho=5110379 (see https://www.gbif.org/occurrence/2996641649).

## Backlinks

https://science.mnhn.fr/institution/um/collection/mpu/item/mpu015018 has link to JSTOR record for same specimen


## GBIF Downloads

Experience some problems with GBIF downloads [Downloads failing to include all files in the archive](https://discourse.gbif.org/t/downloads-failing-to-include-all-files-in-the-archive/4159/15), turns out it’s likely a Safari bug for big zip files, so make sure to download files directly, e.g. using `curl`.

## Reading

Anna Svensson; Global Plants and Digital Letters: Epistemological Implications of Digitising the Directors' Correspondence at the Royal Botanic Gardens, Kew. Environmental Humanities 1 May 2015; 6 (1): 73–102. doi: https://doi.org/10.1215/22011919-3615907

Ryan D (2018) Global Plants: A Model of International Collaboration . Biodiversity Information Science and Standards 2: e28233. https://doi.org/10.3897/biss.2.28233

Ryan, D. (2013), THE GLOBAL PLANTS INITIATIVE CELEBRATES ITS ACHIEVEMENTS AND PLANS FOR THE FUTURE. Taxon, 62: 417-418. https://doi.org/10.12705/622.26

(2016), Global Plants Sustainability: The Past, The Present and The Future. Taxon, 65: 1465-1466. https://doi.org/10.12705/656.38

Smith, G.F. and Figueiredo, E. (2013), Type specimens online: What is available, what is not, and how to proceed; Reflections based on an analysis of the images of type specimens of southern African Polygala (Polygalaceae) accessible on the worldwide web. Taxon, 62: 801-806. https://doi.org/10.12705/624.5


## Scraping

JSTOR, naturally, doesn’t encourage scraping, nor does it make the data available either through an API or download. Hence there are limits to what we can retrieve. Given that all we want is basic metadata (JSTOR identifier, specimen code, taxonomic name) we can use the search results form to search by genus, but this is limited to approximately 1200 records, so large genera may be incomplete.

## Parsing

Once downloaded the web pages are parsed to extract basic metadata. Taxonomic names are parsed using `taxon_name_parser.php` and the data is loaded into a SQLite database.

## Mapping JSTOR identifiers to GBIF

To explore links between JSTOR and GBIF I use [Material Examined](https://material-examined.herokuapp.com), which attempts to map specimen codes to GBIF records. Below I’ve made notes on how successful this is, and which Herbaria codes require additional work to resolve. Some GBIF records also include a collection-specific URL for the specimen (see, e.g., [CETAF Specimen URI Tester](http://herbal.rbge.info/), which we could also extract. This would enable direct linking to the herbarium web site.

### Working

- AK (fixed)
- B (fixed)
- BM
- BR (fixed)
- BRLU (fixed)
- BRIT
- CANB (has versioning? Such as ‘.1’ as a suffix, can also be ‘.2’)
- CAS (will get animals too)
- COL (fixed)
- F (WTF)
- GB (fixed)
- GH
- GZU
- JE
- K
- L
- LD
- LISC (fixed, note that images are poor and not the same as in JSTOR, how come?, e.g. https://www.gbif.org/occurrence/813346859 and https://plants.jstor.org/stable/10.5555/al.ap.specimen.lisc002383)
- MICH
- MPU
- MSC
- NCU (fixed)
- NSW
- NY (fixed)
- P
- PC (fixed)
- RB (fixed)
- S (fixed)
- SANT (fixed, has links that go to a movie!? https://www.usc.gal/herbario/?SANT_10130
- SAV (fixed)
- SBT (fixed)
- U (fixed)
- W (fixed)
- WAG (note we need to use `institutionID` as there is no `institutionCode`)
- WU

### Not working (maybe not in GBIF)

- AMES
- BAA (in GBIF under [Tropicos Specimens Non-MO](https://www.gbif.org/dataset/e053ff53-c156-4e2e-b9b5-4462e9625424), but no code shared with JSTOR (see 10.5555/al.ap.specimen.baa00002080 and https://www.gbif.org/occurrence/4061186547 )
- BKF
- C
- FI
- GOET
- HAL (but some records in via MOBOT!?) Also directly via JACQ, e.g. [HAL0099901](https://hal.jacq.org/HAL0099901)
- HUA in GBIF but not barcodes, would need to match on collectors
- IFAN not in GBIF
- KEP
- LE (Tropicos non MO but no match on barcode, match on collector number)
- LIL
- MU
- PH
- S
- SAM (in GBIF but no institution code, e.g. https://www.gbif.org/occurrence/3708366841)
- SBBG (they have old JSTOR codes in Darwin Core records)
- SING (hosted by Oxford https://herbaria.plants.ox.ac.uk/bol/sing )
- UC
- WIS (mostly not in GBIF, except US material)
- WSY

### Special cases

#### C

C10007766 is https://www.gbif.org/occurrence/125812836 (I think), no shared identifiers, but text looks similar, maybe match notes?

Likewise C10017788 is likely https://www.gbif.org/occurrence/125813592 WHY no images?

#### F

F0BN009917 matches https://www.gbif.org/occurrence/1211544277, which has institution code ‘B’, so Darwin Core is seriously borked.

#### G

G00358419 compare with https://www.gbif.org/occurrence/1144699768 (maybe the same thing, but catalog number is `G-G-242139/2`, but see their own database http://www.ville-ge.ch/imagezoom/?fif=cjbiip/cjb19/img_108/G00358418.ptif&cvt=jpeg https://data.gbif.ch/gbif-portal/#/?search_scientificNameQuery=Tarrietia%20amboinensis&search_observation=true&search_recent=true&search_fossil=true&search_living=true&searchPerformed=true&dataDialog=on&dataId=4274293&dataTabIndex=0

Direct links to specimen records:

https://data.gbif.ch/gbif-portal/Search.action#/?dataDialog=on&GBIFCHID=G-G-87689/1

#### M

“M-0241896 / 724056 / 371814” https://www.gbif.org/occurrence/1848902995 which is M0241896 (sigh)

#### MO
MO-256766 is https://www.gbif.org/occurrence/4032024902, note that 256766 is in the image URL http://images.mobot.org/tropicosthumbnails/Tropicos/345/MO-256766.jpg

#### SBBG

`SBBG000023` is now `SBBG150028`, JSTOR id is preserved in `otherCatalogNumbers`, will need to download and parse files to make the mapping.

#### SING

SING is at Oxford!? Can get image from code:

https://herbaria.plants.ox.ac.uk/bol/SING/image/SING0044039.jpg

Zoomable browser: 
https://herbaria.plants.ox.ac.uk/bol/SING/image/SING0044039_a.jpg/Zoom?fpi=1

https://herbaria.plants.ox.ac.uk/bol/sing/results

While system (BRAHMS) looks to be a nightmare.


#### US

Barcodes are in metadata for the image, will need to download data from GBIF and match on that.


## “Stable” identifiers

[CETAF Stable Identifiers (CSI)](https://cetaf.org/best-practices/cetaf-stable-identifiers-csi-2/)

[CETAF Specimen URI Tester](http://herbal.rbge.info/md.php?q=implementers)

Hyam, R.D., Drinkwater, R.E. & Harris, D.J. Stable citations for herbarium specimens on the internet: an illustration from a taxonomic revision of Duboscia (Malvaceae) Phytotaxa 73: 17–30 (2012). [https://doi.org/10.11646/phytotaxa.73.1.4](https://doi.org/10.11646/phytotaxa.73.1.4)

## Downloads

| herbarium | download | notes | DOI of dataset |
|--|--|--|
| | 0001411-231002084531237 |  BODATSA | 10.15468/2aki0q |
| CAS | 0020513-231002084531237 | CAS Botany (BOT) | 10.15468/7gudyo |
| BM | 0011098-230918134249559 | | 10.5519/0002965 |
| E | 0021070-231002084531237 | | 10.15468/ypoair |
| G | 0009526-230918134249559 | | 10.15468/rvjdu1 |
| G-DC | 0024911-231002084531237 | | 10.15468/s5auru |
| GOET | 0022667-231002084531237 | | 10.15468/9uzqe3 
| K | 0012338-230918134249559 | | 10.15468/ly60bx |
| M | 0023097-231002084531237 | | 10.15468/vgr4kl |
| MO | 0008890-230918134249559 | | 10.15468/hja69f |
| P | 0012346-230918134249559 | | 10.15468/nc6rxy 
| PC | 0012347-230918134249559 | | 10.15468/mywiem |
| SBBG | 0001236-231002084531237 | | 10.15468/adb2bb |
| US | 0005866-230918134249559 | | 10.15468/hnhrg3 |
| | | | |

### Bulk matching use SQL

```
SELECT "UPDATE specimen SET gbif = """ || barcode.gbif || """, occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.canonical = barcode.scientificName
AND specimen.gbif  IS NULL
AND specimen.herbarium="M";
```

Singapore is different because it’s not in GBIF, so had to scrape Brahms in Oxford, then match on barcodes. SING ids become URLs if appended to https://herbaria.plants.ox.ac.uk/bol/sing/record/details/

```
SELECT "UPDATE specimen SET occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.canonical = barcode.canonical
AND specimen.herbarium="SING";

SELECT "UPDATE specimen SET occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.stored_under_name = barcode.canonical
AND specimen.herbarium="SING";

```


## Matching

In matching records by default I am trying to match BARCODE labels in JSTOR with equivalent information in GBIF. As a check we can also compare taxonomic names, but this gets tricky as specimen may be stored more than one name, the names may vary between JSTOR and GBIF, and simple string comparison can be defeated by things such as gender changes. We do some simple stemming to try and catch these.

Boyle, B., Hopkins, N., Lu, Z. et al. The taxonomic name resolution service: an online tool for automated standardization of plant names. BMC Bioinformatics 14, 16 (2013). https://doi.org/10.1186/1471-2105-14-16

Rees T (2014) Taxamatch, an Algorithm for Near (‘Fuzzy’) Matching of Scientific Names in Taxonomic Databases. PLoS ONE 9(9): e107510. https://doi.org/10.1371/journal.pone.0107510

### Bulk matching

The “oh fuck it” strategy:

```
SELECT "UPDATE specimen SET gbif = """ || barcode.gbif || """, occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.canonical = barcode.canonical
AND specimen.herbarium="K";
```

```
SELECT "UPDATE specimen SET gbif = """ || barcode.gbif || """, occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.stored_under_name = barcode.canonical
AND specimen.herbarium="K";
```

```
SELECT "UPDATE specimen SET gbif = """ || barcode.gbif || """, occurrenceID = """ || barcode.id || """ WHERE doi=""" || specimen.doi || """;" 
FROM specimen INNER JOIN barcode ON specimen.code = barcode.barcode 
WHERE specimen.stored_under_name = barcode.scientificName
AND specimen.herbarium="US";
```



### Image matching

Since JSTOR has images, and so does GBIF (mostly) we could also use image matching to check matches are correct. We can access JSTOR thumbnails (full images are typically behind a paywall), and gBIF images are freely available. Need simple way to test whether images are the “same”. See https://stackoverflow.com/questions/23982960/fast-and-efficient-way-to-detect-if-two-images-are-visually-identical-in-python as a starting point, especially https://stackoverflow.com/a/73760220

```
compare -metric phash a00277411.jpg 277411.jpg -compose src delta.png


## Stats

```sql
SELECT COUNT(doi) FROM specimen WHERE type_status IS NOT NULL;
```

1265103
1308834
1354861


### Counts

|Herbarium|Count|
|--|--|
|K|270492|
|P|151207|
|US|91660|
|BM|78142|
|E|74293|
|NY|73067|
|BR|55750|
|G|51974|
|S|48221|
|GH|46181|
|B|45131|
|F|42021|
|M|41344|
|L|39728|
|GDC|38671|
|MA|29291|
|MO|28420|
|LINN|27476|
|PH|25428|
|A|24229|
|MEL|23340|
|MPU|20341|
|SI|19111|
|BISH|18819|
|JE|15959|
|HBG|14781|
|C|14542|
|RSA|13402|
|W|13385|
|TCD|12420|
|PRE|12173|
|COL|12164|
|LE|10928|
|BOL|10676|
|RB|10451|
|FI|10153|
|U|9893|
|GOET|9685|
|HAL|9574|
|CORD|8836|
|CAS|8800|
|LD|8286|
|MICH|8134|
|COI|8090|
|UC|7994|
|MEXU|7683|
|PERTH|7245|
|BRI|7094|
|TUB|6556|
|NDG|6196|
|CANB|5914|
|NSW|5729|
|AMES|5530|
|WAG|5388|
|SAV|4900|
|UBT|4758|
|SPF|4514|
|VEN|4462|
|R|4435|
|SING|4115|
|NBG|4044|
|SGO|3975|
|RM|3730|
|DAO|3699|
|H|3535|
|LL|3527|
|LP|3516|
|SP|3369|
|LISU|3358|
|YBI|3338|
|PRC|3336|
|IFAN|3202|
|FT|3190|
|SBT|3185|
|GZU|3152|
|BJA|3121|
|LIL|3084|
|SEL|3079|
|NU|3073|
|YU|3035|
|BAA|2947|
|QCA|2902|
|EA|2847|
|TOGO|2808|
|WIS|2714|
|LISC|2557|
|AD|2527|
|FHI|2523|
|FR|2479|
|IUK|2418|
|GRA|2370|
|KW|2238|
|VT|2226|
|SAM|2223|
|GENT|2210|
|TEX|2198|
|KATH|2149|
|KFTA|2144|
|CTES|2129|
|MIN|1976|
|CM|1967|
|ILL|1960|
|HUA|1846|
|RAB|1818|
|LECB|1743|
|WU|1688|
|ABFM|1636|
|BNRH|1616|
|PMA|1603|
|WSY|1597|
|GB|1536|
|QCNE|1478|
|AK|1469|
|OSC|1312|
|MSC|1175|
|BC|1151|
|CNS|1144|
|YA|1133|
|CHR|1080|
|ERE|1057|
|ARIZ|1051|
|TBI|1040|
|KIP|1028|
|NH|985|
|HEID|976|
|DNA|964|
|GC|936|
|HNBU|931|
|BRIT|893|
|ENCB|878|
|MG|870|
|IAN|869|
|SRGH|836|
|DUKE|833|
|FLAS|832|
|HOH|776|
|LSHI|775|
|COLO|770|
|LWI|767|
|REG|763|
|MSB|762|
|BK|732|
|AC|723|
|ISC|715|
|HAJB|701|
|LPB|687|
|NHT|682|
|ASU|681|
|NOU|679|
|MAPR|654|
|WTU|650|
|NCU|640|
|USM|604|
|LG|596|
|HO|589|
|KEP|571|
|BAB|549|
|TAN|544|
|JEPS|514|
|EAP|513|
|TEF|511|
|NEB|503|
|PTBG|490|
|JAUM|473|
|MVFA|457|
|MLGU|438|
|NSK|436|
|BKL|424|
|SEV|397|
|BHCB|396|
|MU|384|
|UIU|384|
|PORT|379|
|VALLE|378|
|STU|374|
|IEB|372|
|XAL|371|
|INB|368|
|SD|367|
|LW|348|
|BCN|328|
|BRLU|307|
|MEDEL|299|
|RENO|297|
|NA|296|
|BLH|293|
|ETH|289|
|OS|283|
|PUL|280|
|CHAPA|274|
|GBH|274|
|NMW|269|
|MOL|262|
|LA|256|
|LOJA|248|
|LUKI|248|
|BAF|228|
|DAV|228|
|LEB|223|
|NEBC|220|
|IBUG|219|
|SCZ|218|
|AMD|216|
|MHU|212|
|NO|212|
|OKLA|208|
|CICY|205|
|UBC|197|
|HA|195|
|NT|189|
|VAL|187|
|ALA|186|
|UAMIZ|183|
|TUR|182|
|CAI|180|
|EPU|160|
|MASS|157|
|LMA|144|
|QPLS|144|
|PI|140|
|SANT|136|
|TFC|135|
|CIIDIR|129|
|KISA|129|
|LSU|129|
|CGE|127|
|ECON|126|
|JBAG|126|
|ANSM|123|
|ABH|116|
|ID|111|
|LUBA|111|
|MBM|110|
|UNM|109|
|CS|101|
|HNT|97|
|PSO|97|
|DES|93|
|FMB|90|
|UCSB|88|
|USFS|78|
|AIX|75|
|CHARL|75|
|BKF|74|
|UCR|74|
|NCSC|73|
|CHOCO|70|
|SBBG|70|
|LWS|68|
|GLM|64|
|OSH|64|
|LOU|63|
|IND|62|
|USCH|57|
|AH|56|
|KAW|49|
|PRU|49|
|SALA|47|
|GUAY|45|
|CWU|44|
|DNZ|44|
|LWKS|41|
|UTMC|37|
|V|37|
|LMU|36|
|ASC|35|
|ILLS|32|
|KAG|32|
|AHUC|31|
|LAGU|30|
|VIT|30|
|QMEX|25|
|XALU|25|
|DUL|24|
|BCMEX|23|
|MVM|22|
|HUQ|21|
|MIL|21|
|HCIB|20|
|YALT|19|
|EMMA|18|
|PUR|18|
|CIB|17|
|UNSW|17|
|EALA|16|
|BRVU|15|
|HULE|15|
|CAN|14|
|HEM|14|
|UNEX|14|
|CDA|11|
|CHER|11|
|HUAL|11|
|INEGI|10|
|CLEMS|9|
|UVAL|9|
|Z|9|
|CHEP|8|
|MHES|8|
|CH|7|
|CIMI|7|
|HNMN|7|
|UPOS|7|
|WMU|7|
|BUT|5|
|EBUM|5|
|HSP|5|
|KNOX|5|
|MSUD|5|
|STRI|5|
|UJAT|5|
|WUD|5|
|QUSF|4|
|UCAM|3|
|UVIC|3|
|BAL|2|
|NCBS|2|
|WNC|2|
|MANK|1|



