# Triplestore Indexer

## Introduction

This Drupal 8 or 9's module provide a system to get the Json-LD representation (which is established from [JSON-LD REST Services module]( https://www.drupal.org/project/jsonld)) of any content type and taxonomy in Drupal and index that into [Blazegraph](https://github.com/blazegraph/database/).

## Requirement

* **Server side**: a Tomcat Server with Blazegraph installed as Triplestore. See [the installation guide](https://islandora.github.io/documentation/installation/manual/installing_fedora_syn_and_blazegraph/).
* **Client side**: a Drupal 8 or 9 website with [JSON-LD REST Services module]( https://www.drupal.org/project/jsonld) and [Advanced Queue](https://www.drupal.org/project/advancedqueue)
* Setup RDF mapping for your content types and taxonomy `at admin/config/development/configuration`. Please [see instruction](https://www.drupal.org/docs/8/modules/islandora/user-documentation/rdf-generation)

## Configuration

* Download the module to your Drupal site.
* Enable the module by **Extend > Custom** or using `drush en triplestore_indexer`.
* Go to **Configuration > System > Triplestore Indexer**.
* Fill out the configuration form (please see screenshot below)
  - **Server URL**: Blazegraph server URL, eg. http://example.com:8080/blazegraph or http://example.com:8080/bigdata/
  - **Namespace**: see detail at [here](wiki.blazegraph.com/wiki/index.php/GettingStarted#So_how_do_I_put_the_database_in_triple_store_versus_quad_store_mode.3F).
  - **Method of authentication**:
    + **Basic/Digest**: see [the setup guide](http://www.mtitek.com/tutorials/samples/tomcat-digest-auth.php). If your Tomcat server has authentication enabled, enter username and password.
    + **None**: please proceed to the next step.
  - **Method of operation**:
    + **[Drupal Entity Hooks](https://api.drupal.org/api/drupal/core%21core.api.php/group/hooks/9.0.x)**: By default this option selected, the indexing will be executed immediately after a node or a taxonomy term is [created](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21entity.api.php/function/hook_entity_insert/9.0.x), [update](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21entity.api.php/function/hook_entity_update/9.0.x), or [deleted](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21entity.api.php/function/hook_entity_delete/9.0.x). (*WARNING: it may effect the performance of the site if many nodes/taxonomy terms are being ingested in bulk.*)
    + **[Advanced Queue](https://www.drupal.org/project/advancedqueue)**: Highly Recommended, when a node or a taxonomy term is created, updated, or deleted, the indexing operation will be added to a queue which can be configured to run with Cron job or Drupal Console commnad (eg. drupal advancedqueue:queue:process default). You can create a seperated queue if needed, then enter the new queue's machine name with default in the "Queue" text field below.
  - **When to index**: During ONLY selected event(s), the indexing will be executed.
  - **Content type**: For ONLY selected content type(s), the indexing will be executed.
  - **Taxonomy**: For ONLY selected taxonomy term(s), the indexing will be executed.

    
