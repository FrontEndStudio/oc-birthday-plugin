# October CMS Birtday plugin

## About

This is a Birthday plugin for [October CMS](https://octobercms.com).

## Supported fields

first_name, last_name, middle_name, birth_date, sort_order, status

## Functionality backend

* Create
* Update
* Delete
* Reorder (Sortable)
* Import
* Export

## Plugin properties

### recordList

noRecordsMessage (string) -  Message to display in the list in case there are no records. [No records found]

**Link to details page**
- detailsPage (dropdown) - Page to display record details.
- detailsUrlParameter (string) - [id]

** Pagination **
- recordsPerPage (number): Number of records to display on a single page. Leave empty to disable pagination.
- pageNumber (string) - This value is used to determine what page the user is on [:id]

**Sorting**
- sortColumn (autocomplete): Model column the records should be ordered by
- sortDirection (drodown): [asc|desc]

### recordDetails

- identifierValue (string) - Identifier value to load the record from the database. Specify a fixed value or URL parameter name. [:id]
- modelKeyColumn (autocomplete) - Model column to use as a record identifier for fetching the record from the database. [id]
- notFoundMessage (string) - Message to display if the record is not found. Used in the default component\'s partial. [Record not found]

## How to use this component in October CMS

To use the component, drop it on a page, fill in the plugin properties and use the `{% component 'recordList' %}` and `{% component 'recordDetails' %}` tag anywhere in the page code to render it. The next example shows a simplest page code that uses the birthday component:

```
title = "Birthday List"
url = "/birthday/:id?1"
layout = "full"
is_hidden = 0

[recordList]
noRecordsMessage = "No records found"
detailsPage = "birthday-user"
detailsUrlParameter = "id"
recordsPerPage = 10
pageNumber = "{{ :id }}"
sortColumn = "last_name"
==
{% component 'recordList' %}
```

```
title = "Birthday Detail"
url = "/birthday/user/:id"
layout = "full"
is_hidden = 0

[recordDetails]
identifierValue = "{{ :id }}"
modelKeyColumn = "id"
notFoundMessage = "Record not found"
==
{% component 'recordDetails' %}
```
