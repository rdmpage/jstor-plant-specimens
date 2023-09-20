# JSTOR plant specimens

JSTOR’s [Global Plants](https://plants.jstor.org) includes a database of plant specimens, many of which are types. While many of these records are in GBIF, many aren’t. The code in this repository scrapes JSTOR to build a list of the specimens it has. The goal is to (a) have a list of what JSTOR has, (b) map those records to the equivalent records in GBIF to discover gaps in GBIF coverage, and (c) make it easier to link to JSTOR plant specimens. 

JSTOR uses a DOI-like identifier, e.g. [10.5555/al.ap.specimen.bm000753002](https://plants.jstor.org/stable/10.5555/al.ap.specimen.bm000753002), where `bm000753002` is a combination of herbarium code (see [Global Plants Partners](https://plants.jstor.org/partners)) and the specimen barcode (barcode in this context is a literal barcode, typically visible on the herbarium sheet).

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

B (fixed)
BM
BR (fixed)
BRLU (fixed)
BRIT
CANB (has versioning? Such as ‘.1’ as a suffix, can also be ‘.2’)
CAS (will get animals too)
COL (fixed)
F (WTF)
GB (fixed)
GH
K
L
LD
MICH
MPU
MSC
NY (fixed)
P
RB (fixed)
S (fixed)
SBT (fixed)
U (fixed)
WAG (note we need to use `institutionID` as there is no `institutionCode`)
WU

### Not working (maybe not in GBIF)

AMES
FI
GOET
KEP
LE
LIL
MU
PH
S
SING (hosted by Oxford https://herbaria.plants.ox.ac.uk/bol/sing )
UC

### Special cases

#### C

C10007766 is https://www.gbif.org/occurrence/125812836 (I think), no shared identifiers, but text looks similar, maybe match notes?

#### F

F0BN009917 matches https://www.gbif.org/occurrence/1211544277, which has institution code ‘B’, so Darwin Core is seriously borked.

#### G

G00358419 compare with https://www.gbif.org/occurrence/1144699768 (maybe the same thing, but catalog number is `G-G-242139/2`, but see their own database http://www.ville-ge.ch/imagezoom/?fif=cjbiip/cjb19/img_108/G00358418.ptif&cvt=jpeg https://data.gbif.ch/gbif-portal/#/?search_scientificNameQuery=Tarrietia%20amboinensis&search_observation=true&search_recent=true&search_fossil=true&search_living=true&searchPerformed=true&dataDialog=on&dataId=4274293&dataTabIndex=0

#### M

“M-0241896 / 724056 / 371814” https://www.gbif.org/occurrence/1848902995 which is M0241896 (sigh)

#### MO
MO-256766 is https://www.gbif.org/occurrence/4032024902, note that 256766 is in the image URL http://images.mobot.org/tropicosthumbnails/Tropicos/345/MO-256766.jpg

#### US

Barcodes are in metadata for the image, will need to download data from GBIF and match on that.


## “Stable” identifiers

[CETAF Specimen URI Tester](http://herbal.rbge.info/md.php?q=implementers)

Hyam, R.D., Drinkwater, R.E. & Harris, D.J. Stable citations for herbarium specimens on the internet: an illustration from a taxonomic revision of Duboscia (Malvaceae) Phytotaxa 73: 17–30 (2012). [PDF]
