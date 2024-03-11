# Demographic Module

The demographic module works with three main resources to compute and provide demographic indexes for properties, to be used inside of the Planner.
These resources all follow the same structure and similar lifecycle.

All resources have three level:
    - **Template**: Describe how each resource should be created
    - **Resource**: The resource itself, points to the template used to create it, and links to the property it is attached to. Also holds additional metadata depending on the type.
    - **Values**: All the values of the resource itself.

The resources are as follows:

### Geographic Reports

A geographic report is a weighted list of areas. The different types of geographic reports denotes how they are built. It has one value per area it includes. The same area can be included multiple times.

- **Customer**: The report is built from an existing customer file that has to be provided. As of now, only Environics MobileScape Customer files are supported
- **Area:Radius**: Built using the lng/lat of the property the report is attached to and a radius specified in the template
- **Area:Isochrone**: Built using an isochrone polygon generated using parameters given in the template
- **Area:Custom**: Built using a custom polygon given at creation

Upon processing geographic reports, the source data gets translated to a list of areas and weight. For Customer files, the source row that pointed to each area is preserved in the metadata of each value.

Geographic Reports Templates uses configuration blocks (`GeographicReportTemplateConfiguration`) for targeting and configuration. One template can have multiple configuration blocks, they are processed in order of weight. A higher weight, means a higher priority.
A property will only match with a single configuration block. It is therefore possible to have different parameters for different properties in a single template.

### Extracts

An Extract represent the dataset datapoints values calculated for a specific geographic report. It has as many values as there are datapoints in the dataset version it is associated to.
Extracts templates specify a geographic report template and a dataset version. 

They are automatically generated and processed as geographic reports become available.

### Index sets
Index sets is a combination of two extracts to obtain indexes, telling how one extract relate to another. They have as many values as there's datapoints in the extracts used. Both extracts must be for the same dataset version. 

Index sets are generated and processed as extracts become available.

## Lifecycle

Some of these resource take some time to process as they imply large amount of information. Therefore, all treatments should occur at night when the system is used the least.

Once a template as been created, all resources follow the same steps:

- Generation phase
  - Look for properties that don't already have a resource for this template, and that fits the requirements
  - Generate an empty resource, with status `PENDING`
- Processing phase
  - Execute the necessary actions to generate the values for the resource.
  - Once done, set the resource status as `DONE`

It is possible for users to short-circuit this process by generating resources themselves, as well as forcing the processing of a resource during the day.

## Storage

All raw data (datasets and their values), and geographic reports and extracts are stored in the Demographic DB, which is a Postgres instance separate from the main Connect database. 
Only Index sets are stored in the main DB for quick access.

