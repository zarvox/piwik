# 2.15.0-sandstorm-5

Moves the MySQL tmpdir from the 16MB tmpfs on /tmp to /var/tmp.
Larger grains may need this space when doing analytics over larger amounts of data.

# 2.15.0-sandstorm-4

Avoids prompting tracked users for HTTP Basic Auth credentials when they view a
page with the Piwik script embedded.  This was triggered by a recent change in
Sandstorm's API endpoint behavior.

# 2.15.0-sandstorm-3

Enabled LOAD DATA INFILE on MySQL to make bulk import/export work.
Enabled arbitrarily large upload, for data import.

# 2.15.0-sandstorm-2

Fixed a bug that caused all clients to appear to have IP 127.0.0.0/24.

# 2.15.0-sandstorm-1

Initial release of Piwik package for Sandstorm.
