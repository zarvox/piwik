@0xa30433ef7e7027fa;

using Spk = import "/sandstorm/package.capnp";
# This imports:
#   $SANDSTORM_HOME/latest/usr/include/sandstorm/package.capnp
# Check out that file to see the full, documented package definition format.

const pkgdef :Spk.PackageDefinition = (
  # The package definition. Note that the spk tool looks specifically for the
  # "pkgdef" constant.

  id = "xuajusd5d4a9v4js71ru0cwj9wn984q1x8kny10htsp8f5dcfep0",
  # Your app ID is actually its public key. The private key was placed in
  # your keyring. All updates must be signed with the same key.

  manifest = (
    # This manifest is included in your app package to tell Sandstorm
    # about your app.

    appTitle = (defaultText = "Piwik Web Analytics"),

    appVersion = 3,  # Increment this for every release.

    appMarketingVersion = (defaultText = "2.15.0-sandstorm-3"),
    # Human-readable representation of appVersion. Should match the way you
    # identify versions of your app in documentation and marketing.

    actions = [
      # Define your "new document" handlers here.
      ( title = (defaultText = "New web analytics"),
        command = .myCommand,
        # The command to run when starting for the first time. (".myCommand"
        # is just a constant defined at the bottom of the file.)
        nounPhrase = (defaultText = "web analytics"),
      )
    ],

    continueCommand = .myCommand,
    # This is the command called to start your app back up after it has been
    # shut down for inactivity. Here we're using the same command as for
    # starting a new instance, but you could use different commands for each
    # case.

    metadata = (
      icons = (
        appGrid = (svg = embed "piwik-128.svg"),
        grain = (svg = embed "piwik-24.svg"),
        market = (svg = embed "piwik-150.svg"),
      ),
      website = "http://piwik.org/",
      codeUrl = "https://github.com/zarvox/piwik",
      license = (openSource = gpl3),
      categories = [webPublishing],
      author = (
        contactEmail = "drew@sandstorm.io",
        pgpSignature = embed "pgp-signature",
        upstreamAuthor = "Piwik Core Team",
      ),
      pgpKeyring = embed "pgp-keyring",

      description = (defaultText = embed "description.md"),
      shortDescription = (defaultText = "Web Analytics"),
      screenshots = [
        (width = 746, height = 795, png = embed "screenshot-1.png"),
        (width = 1677, height = 871, png = embed "screenshot-2.png"),
      ],
      changeLog = (defaultText = embed "changelog.md"),
    ),
  ),

  sourceMap = (
    # Here we defined where to look for files to copy into your package. The
    # `spk dev` command actually figures out what files your app needs
    # automatically by running it on a FUSE filesystem. So, the mappings
    # here are only to tell it where to find files that the app wants.
    searchPath = [
      ( sourcePath = "." ),  # Search this directory first.
      ( sourcePath = "/",    # Then search the system root directory.
        hidePaths = [ "home", "proc", "sys",
                      "etc/passwd", "etc/hosts", "etc/host.conf",
                      "etc/nsswitch.conf", "etc/resolv.conf",
                      "opt/app/.git", "opt/app/.sandstorm/.vagrant",
        ],
        # You probably don't want the app pulling files from these places,
        # so we hide them. Note that /dev, /var, and /tmp are implicitly
        # hidden because Sandstorm itself provides them.
      )
    ]
  ),

  fileList = "sandstorm-files.list",
  # `spk dev` will write a list of all the files your app uses to this file.
  # You should review it later, before shipping your app.

  alwaysInclude = [
    "opt/app"
  ],
  # Fill this list with more names of files or directories that should be
  # included in your package, even if not listed in sandstorm-files.list.
  # Use this to force-include stuff that you know you need but which may
  # not have been detected as a dependency during `spk dev`. If you list
  # a directory here, its entire contents will be included recursively.

  bridgeConfig = (
    viewInfo = (
      permissions = [(
          name = "superuser",
          title = (defaultText = "superuser"),
          description = (defaultText = "grants Piwik server-config privileges")
        ),(
          name = "admin",
          title = (defaultText = "admin"),
          description = (defaultText = "grants Piwik administrative privileges")
        ),(
          name = "view",
          title = (defaultText = "view"),
          description = (defaultText = "grants Piwik administrative privileges")
        )
      ],
      roles = [(
          title = (defaultText = "admin"),
          permissions = [false, true, true],
          verbPhrase = (defaultText = "can change settings"),
          default = true
        ),(
          title = (defaultText = "viewer"),
          permissions = [false, false, true],
          verbPhrase = (defaultText = "can view analytics data")
        ),(
          title = (defaultText = "trackee"),
          permissions = [false, false, false],
          verbPhrase = (defaultText = "can be tracked")
        ),
      ]
    ),
    apiPath = "/piwik.php"
  )
);

const myCommand :Spk.Manifest.Command = (
  # Here we define the command used to start up your server.
  argv = ["/sandstorm-http-bridge", "8000", "--", "/opt/app/.sandstorm/launcher.sh"],
  environ = [
    # Note that this defines the *entire* environment seen by your app.
    (key = "PATH", value = "/usr/local/bin:/usr/bin:/bin")
  ]
);
