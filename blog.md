

This blog post documents my attempts to create links between two major resources for plant taxonomy: JSTOR’s [Global Plants](https://plants.jstor.org) and [GBIF](https://www.gbif.org), specifically between type specimens in JSTOR and the corresponding occurrence in GBIF. The TL;DR is that I have tried to map 1,354,861 records for type specimens from JSTOR to the equivalent record in GBIF, and managed to find 903,945 (67%) matches.

Why do this? Partly because a collaborator asked me, but I've long been interested in JSTOR’s Global Plants. This was a massive project to digitise plant type specimens all around the world, generating millions of images of herbarium sheets. It also resulted in a standardised way to refer to a specimen, namely its barcode, which comprises the herbarium code and a number (typically padded to eight digits). These barcodes are converted into JSTOR URLs, so that E00279162 becomes [https://plants.jstor.org/stable/10.5555/al.ap.specimen.e00279162](https://plants.jstor.org/stable/10.5555/al.ap.specimen.e00279162). These same barcodes have become the basis of efforts to create stable identifiers for plant specimens, for example [https://data.rbge.org.uk/herb/E00279162](https://data.rbge.org.uk/herb/E00279162).

JSTOR created an elegant interface to these specimens, complete with links to literature on JSTOR, BHL, and links to taxon pages on GBIF and elsewhere. It also added the ability to comment on individual specimens using [Disqus](https://disqus.com).

[img]

However, JSTOR Global Plants is not open. If you click on a thumbnail image of a herbarium sheet you hit a paywall.

[img]

In contrast data in GBIF is open. The table below is a simplified comparison of JSTOR and GBIF.

| Feature | JSTOR | GBIF |
|--|--|--|
| Open or paywall | Paywall | Open |
| Consistent identifier | Yes | No |
| Images | All specimens | Some specimens |
| Types linked to original name | Yes | Sometimes |
| Community annotation | Yes | No |
| Can download the data | No | Yes |
| API | No | Yes |

JSTOR offers a consistent identifier (the barcode), an image, has the type linked to the original name, and community annotation. But there is a paywall, and no way to download data. GBIF is open, enables both bulk download and API access, but often lacks images, and as we shall see below, the identifiers for specimens are a hot mess.

The "Types linked to original name" feature concerns whether the type specimen is connected to the appropriate name. A type is (usually) the type specimen for a single taxonomic name. For example, E00279162 is the type for **Achasma subterraneum** Holttum. This name is now regarded as a synonym of **Etlingera subterranea** (Holttum) R.M.Sm. following the transfer to the genus **Etlingera**. But E00279162 is not a type for the name **Etlingera subterranea**. JSTOR makes this clear by stating that the type is stored under **Etlingera subterranea** but is the type for **Achasma subterraneum**. However, this information does not make it to GBIF, which tells us that E00279162 is a type for [**Etlingera subterranea** ](https://www.gbif.org/species/2760154) and that it knows of no type specimens for [**Achasma subterraneum**](https://www.gbif.org/species/2760155). Hence querying GBIF for type specimens is potentially fraught with error.

Hence JSTOR has often cleaner and more accurate data. But it is behind a paywall. Hence I set about to get a list of all the type specimens that JSTOR has, and try and match those to GBIF. This would give me a sense of how much content behind JSTOR's paywall was freely available in GBIF, as well as how much content JSTOR had that was absent from GBIF. I also wanted to use JSTOR's reference to the original plant name to get around any GBIF'\s tendency to link types to the wrong name.

## Challenges

Mapping JSTOR barcodes to records in GBIF proved challenging. In an ideal world specimens would have a single identifier that everyone would use when citing or otherwise referring to that specimen. Of course this is not the case. There are all manner of identifiers, ranging from barcodes, collector names and numbers, local database keys (integers, UUIDs, and anything in between). Some identifiers include version codes. All of this greatly complicates linking barcodes to GBIF records. I made extensive use of my [Material examined](https://material-examined.herokuapp.com) tool that attempts to translate specimen codes into GBIF records. Under the hood this means lots of regular expressions, and I spent a lot of time adding code to handle all the different ways herbaria manage to mangle barcodes.

In some cases JSTOR barcodes are absent from the specimen information in the GBIF occurrence record itself but are hidden in metadata for the image (such as the URL to the image). My "Material examined" tool uses the GBIF API, and that doesn't enable searches for parts of image URLs. Hence for some herbaria I had to download the archive, extract media URLs and look for barcodes. In the process I encountered a subtle bug in Safari that truncated downloads, see [Downloads failing to include all files in the archive](https://discourse.gbif.org/t/downloads-failing-to-include-all-files-in-the-archive/4159).

Some herbaria have data in both JSTOR and GBIF, but no identifiers in common (other than collector names and numbers, which would require approximate string matching). But in some cases the herbaria have their own web sites which mention the JSTOR barcodes, as well as the identifiers those herbaria do share with GBIF. In these cases I would attempt to scrape the herbaria web sites, extract the barcode and original identifier, then find the original identifier in GBIF.

Another observation is that in some cases the imagery in JSTOR is not the same as GBIF. For example [LISC002383](https://plants.jstor.org/stable/10.5555/al.ap.specimen.lisc002383) and [GBIF:813346859]( https://www.gbif.org/occurrence/813346859) are the same specimens but the images are different. Why are the images provided to JSTOR not being provided to GBIF?

[imgs]

In some cases there 

In the process of making this mapping it became clear that there are herbaria that aren't in GBIF, for example Singapore (SING) is not in GBIF but instead is hosted at Oxford University (!) at [https://herbaria.plants.ox.ac.uk/bol/sing](https://herbaria.plants.ox.ac.uk/bol/sing). There seem to be a number of herbaria that have content in JSTOR but not GBIF, hence GBIF has gaps in its coverage of type specimens.

Interestingly JSTOR rarely seems to be a destination for links. An exception is the Paris museum, for example specimens [MPU015018](https://science.mnhn.fr/institution/um/collection/mpu/item/mpu015018) has a link to JSTOR record for same specimen [MPU015018](http://plants.jstor.org/specimen/MPU015018).


## Matching taxonomic names

As a check on matching JSTOR to GBIF I would also check that the taxonomic names associated with the two records are the same. The challenge here is that the names may have changed. Ideally both JSTOR and GBIF would have either a history of name changes, or at least the original name the specimen was associated with (i.e., the name for which the specimen is the type). And of course, this isn't the case. So I relied on a series of name comparisons, such as "are the names the same?", "if names are different are the specific epithets the same?", and "if names are specific epithets are different are the generic names the same?". Because the spelling of species names can change depending on the gender of the genus, I also used some stemming rules to catch names that were the same even if their ending was different. 

This approach will still miss some matches, such as hybrid names, and cases where a specimen is stored under a completely different name (e.g., the original name is a heterotypic synonym of a different name). 

## Mapping

The mapping made so far is available on GitHub [https://github.com/rdmpage/jstor-plant-specimens](https://github.com/rdmpage/jstor-plant-specimens) and Zenodo [https://doi.org/10.5281/zenodo.10044359](https://doi.org/10.5281/zenodo.10044359). 

At the time of writing I have retrieved 1,354,861 records for type specimens from JSTOR, of which 903,945 (67%) have been matched to GBIF.


## Reading

- Boyle, B., Hopkins, N., Lu, Z. et al. The taxonomic name resolution service: an online tool for automated standardization of plant names. BMC Bioinformatics 14, 16 (2013). https://doi.org/10.1186/1471-2105-14-16

- [CETAF Stable Identifiers (CSI)](https://cetaf.org/best-practices/cetaf-stable-identifiers-csi-2/)

- [CETAF Specimen URI Tester](http://herbal.rbge.info/md.php?q=implementers)

- Holttum, R. E. (1950). The Zingiberaceae of the Malay Peninsula. Gardens’ Bulletin, Singapore, 13(1), 1-249. [https://biostor.org/reference/163926](https://biostor.org/reference/163926)

- Hyam, R.D., Drinkwater, R.E. & Harris, D.J. Stable citations for herbarium specimens on the internet: an illustration from a taxonomic revision of Duboscia (Malvaceae) Phytotaxa 73: 17–30 (2012). [https://doi.org/10.11646/phytotaxa.73.1.4](https://doi.org/10.11646/phytotaxa.73.1.4)

- Rees T (2014) Taxamatch, an Algorithm for Near (‘Fuzzy’) Matching of Scientific Names in Taxonomic Databases. PLoS ONE 9(9): e107510. https://doi.org/10.1371/journal.pone.0107510

- Ryan D (2018) Global Plants: A Model of International Collaboration . Biodiversity Information Science and Standards 2: e28233. https://doi.org/10.3897/biss.2.28233

- Ryan, D. (2013), THE GLOBAL PLANTS INITIATIVE CELEBRATES ITS ACHIEVEMENTS AND PLANS FOR THE FUTURE. Taxon, 62: 417-418. [https://doi.org/10.12705/622.26](https://doi.org/10.12705/622.26)

- (2016), Global Plants Sustainability: The Past, The Present and The Future. Taxon, 65: 1465-1466. [https://doi.org/10.12705/656.38](https://doi.org/10.12705/656.38)

- Smith, G.F. and Figueiredo, E. (2013), Type specimens online: What is available, what is not, and how to proceed; Reflections based on an analysis of the images of type specimens of southern African Polygala (Polygalaceae) accessible on the worldwide web. Taxon, 62: 801-806. [https://doi.org/10.12705/624.5](https://doi.org/10.12705/624.5)

- Smith, R. M. (1986). New combinations in Etlingera Giseke (Zingiberaceae). Notes from the Royal Botanic Garden Edinburgh, 43(2), 243-254.

- Anna Svensson; Global Plants and Digital Letters: Epistemological Implications of Digitising the Directors' Correspondence at the Royal Botanic Gardens, Kew. Environmental Humanities 1 May 2015; 6 (1): 73–102. doi: [https://doi.org/10.1215/22011919-3615907](https://doi.org/10.1215/22011919-3615907)

