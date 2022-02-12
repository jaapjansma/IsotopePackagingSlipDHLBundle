# Isotope Packaging Slip DHL Bundle

Contao Isotope Packaging Slip DHL Bundle.

This adds the possibility to automatically create shipments in DHL for packaging slips.

## Requirements

* Contao > 4.9
* Isotope
* [Isotope Packaging Slip Bundle](https://packagist.org/packages/krabo/isotope-packaging-slip-bundle)

## Configuration options:

The following configuration can be adjusted to your needs and added to your config.yml file.

```yaml 
isotope_packaging_slip_dhl:
  # API Key - Required
  key: '...'
  # Your user ID - Required
  user_id: '...'
  # Your company and address details. 
  # Required.
  shipper_company_name: 'My Company'
  shipper_street: 'Street'
  shipper_housenumber: '1'
  shipper_postal_code: '1234 AA'
  shipper_city: 'Amsterdam'
  shipper_country_code: 'NL'
  # If you want to display a google maps during the parcel selection
  # then you need provide a google maps api key. This is optional.
  google_maps_api_key: '...'
  # Not needed - your HS code for custom
  hscode: '...'

```

## Contributions

Contributions to this bundle are more than welcome. Please submit your contributions as a merge request.

## License

The extension is licensed under [AGPL-3.0](LICENSE.txt)

## See also

* https://api-gw.dhlparcel.nl/docs/
* https://static.dhlparcel.nl/components/servicepoint-locator-component%40latest/