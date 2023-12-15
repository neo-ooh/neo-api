# The Properties Module

The properties module is a specialized inventory system for OOH advertising needs.
It is called the `properties` module for legacy reasons, but is actually an inventory system.

## Glossary

* **Product**: A sellable entity. A product represent one or more digital screens, or one or more static faces.
* **Property**: A physical location with products
* **Property** Network/Operational Network: A way of categorizing properties for operational/marketing needs
* **Flight**: Sub-section of a contract defined by a type of sale, a period between two dates, a name, and containing a list of products sold for the flights dates

## Architecture

Products are defined by a lot of different values. Some are coming from the property they belong to, some from the product category, and some are specific to them. Almost all of
them can be overridden at the product level if needed.

All resources inside the inventory system with an `inventory_resource_id` can be replicated to other inventory system, and can have representation ids attached to them.

### Inheritance

As said previously, a product is composed of a lot of different properties, that can come from different sources. Here's a breakdown of them

| Product's property   | Origin           | Overridable | Description                                                                                                       |
|----------------------|------------------|-------------|-------------------------------------------------------------------------------------------------------------------|
| Format               | Category         | ✓           | Display format used by this product                                                                               |
| Site type            | Property         | ✓           | Physical location description                                                                                     |
| Quantity             | -                | ✓           | Number of screen/faces of the product                                                                             |
| Is Sellable          | Property         | -           | Tell if the product can be sold. The property AND the product needs to be sellable for the product to be sellable |
| Pricelist            | Property         | x           | Specify specific price/cpm for the product, can only be set at the property level, and is not overridable         |
| Price                | Itself/Pricelist | x           | Cannot be changed manually, can only be updated through a pricelist                                               |
| Is Bonus             | -                | -           | Cannot be changed manually                                                                                        |
| Media Types          | Category         | ✓           | The type of files allowed in this product, an empty list means inherited from the category                        |
| Audio Support        | Category         | ✓           | Tell if the product can play videos with audio                                                                    |
| Motion allowed       | Category         | ✓           | Tell if the product is allowed to play videos with movements                                                      |
| Production cost      | Category         | ✓           | Additional fixed cost when selling the product                                                                    |
| Programmatic Price   | Category         | ✓           | Specific price to use on Programmatic inventories                                                                 |
| Screen Size (inches) | Category         | ✓           | Screen/face diagonal size                                                                                         |
| Screen Type          | Category         | ✓           | LCD, LED, etc. Empty means same as category                                                                       |
| Impressions models   | Category         | ✓           | How to calculate impressions                                                                                      |
| Loop configuration   | Format           | x           | Length of spots, amount of spots in the loop                                                                      |
| Unavailabilities     | Property         | ✓           | Period during which the product is not available. Cumulative with the ones coming from the property               |

### Third party Inventories

Connect is able to synchronize its own inventory with third-party inventories. Connect supports multiple adapters to Connect to external systems, that can be configured to ever
push or pull values. Each connected inventory can then be enabled at will on a per-product basis. It is therefore possible to use Connect to centralize inventories coming from
different platforms, or to replicate all or part of Connect's inventory to multiple other platforms.

Because every inventory system works differently, the actual inventory system is divided in multiple capabilities, allowing each adapter to support what it can, while still
remaining mostly compatible with the whole system. Some capabilities reflect the availability of actions (create/read/etc.) while others denote the presence of certain types of
information. The later are used to differentiate between a value not set and a value not supported. There is a big difference between a product whose screen type can be set but has
not been, versus a product whose screen type cannot be set.

| Capability                 | Description                                                                                                                   | Odoo (Neo) | Hivestack | Reach | Vistar | PlaceExchange | Dummy |
|----------------------------|-------------------------------------------------------------------------------------------------------------------------------|------------|-----------|-------|--------|---------------|-------|
| Products • Read            | Ability to list and read products                                                                                             | ✓          | ✓         | ✓     | ✓      | ✓             |       |
| Products • Write           | Ability to create/update/delete products                                                                                      |            | ✓         | ✓     | ✓      | ✓             |       |
| Products • Quantity        | A single product may represent multiple physical screens/faces, the `quantity` value will mean something                      | ✓          |           |       |        |               |       |
| Products • Media Types     | Products can have supported media types specified                                                                             |            | ✓         |       |        |               |       |
| Products • Audio Support   | Products can have audio support specified                                                                                     |            |           | ✓     |        |               |       |
| Products • Motion Support  | Products can have motion support specified                                                                                    |            |           | ✓     |        |               |       |
| Products • Screen Type     | Products can have their screen type specified                                                                                 |            |           | ✓     |        |               |       |
| Products • Screen Size     | Products can have their screen size specified                                                                                 |            | ✓         | ✓     |        | ✓             |       |
| Products Categories • Read | The inventory has the concept of categories for products, and they can be listed                                              | ✓          |           |       |        |               |       |
| Properties • Read          | The inventory has the concept of properties grouping products, and they can be listed                                         | ✓          | ✓         |       |        |               |       |
| Properties • Product       | The inventory API architecture allows for querying the products belonging to a specific property without parsing all products | ✓          |           |       |        |               |       |
| Properties • Type          | Properties can have a type specified                                                                                          |            | ✓         | ✓     | ✓      | ✓             |       |
| Contracts • Read           | The inventory stores contract and its possible to list them and read them                                                     | ✓          |           |       |        |               |       |
| Contracts • Write          | The inventory stores contract and its possible create them / write them                                                       | ✓          |           |       |        |               |       |

Every inventory, depending on their capability, can be used to either pull information towards Connect, or push information from Connect to it. To keep the association between the
resource in Connect and its representation in the inventory, each adapter can store IDs with context with the resource inside Connect. Also, because not all inventory operate at
the same level, a single Product in Connect may be represented by multiple entries in another inventory. Finally, each inventory system uses its own terminology.

| Resource | Odoo     | Hivestack       | Reach           | Vistar         | PlaceExchange   |
|----------|----------|-----------------|-----------------|----------------|-----------------|
| Product  | Product  | Unit (Location) | Screen (Player) | Venue (Player) | AdUnit (Player) |
| Property | Property | Property        | -               | -              | -               |

### Contracts

Connect has a limited contract support, targeted towards scheduling and monitoring.

A **contract** is a collection of flights owned by a salesperson (a Connect user) and can additionally be defined by an advertiser and a client. A contract's dates are derived from
the contract's flights.
A **flight** is a part of contract. Defined by specific dates, a type of sale and an optional, though highly recommended, name. All products in a contract belong to a flight.

Connect does not support directly creating contract from scratch, only importing them. This is a limitation coming from operating reasons, not technical one. Nothing would prevent
Connect from being able to create new contracts from scratch.

A flight's products are defined by multiple values:

* The sold media value
* The net investment: how much the client paid for this specific product
* The contracted impressions: How many impressions this product should generate for this flight
* The Delivered Repetitions: How many repetitions this product has generated up until now for this contract
* The Delivered Impressions: How many impressions this product has generated up until now for this contract, to compare with the amount of contracted impressions.

#### Import

Contract are imported through an Inventory adapter that delivers all the contract information – header & lines – in a standard format. Connect also supports pulling a compiled
campaign planner plan that may have been attached to the contract as well.

In case a plan is attached with the contract, Connect will use the flights defined in the plan to re-associate the line to their respective flights. If no plan is available, or if
a line does not match any of the known flights, Connect will create new ones as needed.

#### Performances

To collect delivered impressions and allow for (almost) real-time monitoring of contracts delivery, Connect gathers impressions through two means.

* Through direct association with BroadSign reservations. This allows collection impressions from BroadSign campaigns that have not been scheduled in Connect. Reservations
  available for association are selected based on their name. The contract ID must be present for Connect to pick them up and make them available.
* Or through campaigns inside Connect itself, as they already have a mechanism, to fetch performances.
