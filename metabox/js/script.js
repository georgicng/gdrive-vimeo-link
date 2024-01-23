var vimeoCache = {};
// Use the Google API Loader script to load the google.picker script.
function loadPicker() {
  event.preventDefault();
  gapi.load("picker", { callback: onPickerApiLoad });
}

function onPickerApiLoad() {
  createPicker();
}

// Create and render a Picker object for searching images.
function createPicker() {
  if (gdrive.clientToken) {
    var view = new google.picker.View(google.picker.ViewId.DOCS_VIDEOS);
    var picker = new google.picker.PickerBuilder()
      .enableFeature(google.picker.Feature.NAV_HIDDEN)
      .enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
      .setAppId(gdrive.appId)
      .setOAuthToken(gdrive.clientToken)
      .addView(view)
      //.addView(google.picker.ViewId.VIDEO_SEARCH)
      .setDeveloperKey(gdrive.developerKey)
      .setCallback(pickerCallback)
      .build();
    picker.setVisible(true);
  }
}

function pickerCallback(data) {
  var url, name;
  if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
    var doc = data[google.picker.Response.DOCUMENTS][0];
    url = doc[google.picker.Document.URL];
    name = doc.name;
    var param = {
      action: gdrive.uploadAction,
      fileId: doc.id,
      name: name,
      url: url,
      postId: gdrive.postId ? gdrive.postId : jQuery("#post_ID").val(),
    };
    jQuery("#result").text("Uploading...");
    jQuery
      .post(gdrive.ajaxUrl, param)
      .done(function (returnedData) {
        console.log(returnedData);
        jQuery("#result").text("File queued for upload");
        //document.location.reload(true);
        window.location = window.location;
      })
      .fail(function (returnedData) {
        console.error(returnedData);
        alert("error");
      });
  }
}

function loadBrowser() {
  showFiles();
}

function getPage(path) {
  if (path && (match = path.match(/^\/(\d+)$/))) {
    return match[1];
  }
  return 1;
}

function getDirectories(pages, current) {
  return Array.from({ length: pages }, (v, k) => k + 1).filter(
    (item) => item !== current
  );
}

function transformFiles(data) {
  return data
    .filter((item) => item.type === "video")
    .map((item) => {
      const id = item.uri.split("/videos/")[1];
      return {
        id,
        uri: item.uri,
        title: item.name,
        name: `${item.name}.mp4`,
      };
    });
}

function getURI(path, page) {
  const filename = path.split("/").pop();
  if (vimeoCache[page]) {
    var item = vimeoCache[page].find(item => item.title === filename);
    return item.uri;
  }

  throw new Error('Something went wrong!');
}

function showFiles() {
  var browse = jQuery("#browser").browse({
    root: "/",
    separator: "/",
    contextmenu: false,
    dir: function (path) {
      return new Promise(function (resolve, reject) {        
        var page = getPage(path);
        if (page === 1 && path !== "/") {
          reject()
          return;
        }

        if (vimeoCache[page]) {
          resolve({ dirs: vimeoCache["pages"] || [], files: vimeoCache[page].map(item => item.title) });
          return;
        }

        jQuery
          .post(gdrive.ajaxUrl, {
            action: gdrive.browseAction,
            ...(page !== 1 ? { page } : {}),
          })
          .done(function (response) {
            var recordset = response.data;
            var dirs = [];
            if (page === 1) {
              var pages = Math.ceil(recordset.total / recordset.per_page);
              dirs = getDirectories(pages, page);
              vimeoCache["pages"] = dirs;
            }
            var files = [];
            files = transformFiles(recordset.data);
            vimeoCache[page] = files;
            resolve({
              dirs,
              files: files.map(item => item.title),
            });
          })
          .fail(function (returnedData) {
            console.error(returnedData);
            reject();
          });
      });
    },
    open: function (filepath) {
      var path = browse.path();
      var page = getPage(path);
      jQuery
        .post(gdrive.ajaxUrl, {
          uri: getURI(filepath, page),
          action: gdrive.selectAction,
          postId: gdrive.postId ? gdrive.postId : jQuery("#post_ID").val(),
          ...(page !== 1 ? { page } : {}),
        })
        .done(function () {
          //document.location.reload(true);
          window.location = window.location;
        })
        .fail(function (returnedData) {
          console.error(returnedData);
          alert("error");
        });
    },
  });
}
