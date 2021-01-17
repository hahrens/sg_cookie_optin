# Things to take care of for each release

- If you are doing a new feature release (changing the 2nd number in the version x.x.x), go to
Classes/Service/LicenseCheckService and change the version number in the constant CURRENT_VERSION.
- In the same file add the timestamp in the array $versionToReleaseTimestamp. You can get your current timestamp in your
browser console by executing this command: `Math.floor(new Date().getTime()/1000)`

