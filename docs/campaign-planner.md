# The Campaign Planner

Connect's Campaign Planner is a robust tool to build out-of-home and mobile advertising campaigns.

It makes use of the large amount of information known about products and properties to provide a powerful selection, filtering and calculation system to build contracts.

## Save structure

Campaign planner saves exist in two flavours: regular saves, that contain all the configuration, selection and settings decided by the user to build its plan as well as the
computed values for each flight – `.plan` files; and compiled saves, that only holds the computed values – `.ccp` files.

`.plan` and `.ccp` files are actually regular JSON files. All `.plan` files are reference in the DB for easy browsing and searching.

When a user wants to open a plan, the front-end directly downloads the `.plan` file. When opening/parsing a `.plan` file in the back-end,
the `\Neo\Resources\CampaignPlannerPlan\CPPlan` structure should be used. `.ccp` plan make use of the `\Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan` structure.

Because campaign planner saves structure evolves other time, it is necessary when reading a save to properly update it if it is an old version. The front-end has all the mechanism
to update `.plan` file on read as this is where they are primarily used, while the back-end has the logic to updated old `.ccp` files, as they are mostly used there. Ultimately,
update scripts should up to date on both ends.

### Data

The Campaign planner make use of a lot of data to helps users build plans. Notably geographical census boundaries. Theses are stored in the DB and require some special handling
because of their size. This is the `census_division`, `census_subdivision`, `census_federal_electoral_districts`, `census_forward_sortation_area` tables. As of now, the actual
geometry of each boundary is stored in the database alongside their metadata.
