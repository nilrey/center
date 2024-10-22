
  function encodeImageFileAsURL(elemId) {

    var filesSelected = document.getElementById("fileUpload__"+elemId).files;
    if (filesSelected.length > 0) {
      var fileToLoad = filesSelected[0];

      var fileReader = new FileReader();

      fileReader.onload = function(fileLoadedEvent) {
        var srcData = fileLoadedEvent.target.result; // <--- data: base64

        var newImage = document.createElement('img');
        newImage.src = srcData;

        document.getElementById("img__"+elemId).innerHTML = newImage.outerHTML;
        document.getElementById(elemId).value = document.getElementById("img__"+elemId).innerHTML;
        // alert("Converted Base64 version is " + document.getElementById("imgTest").innerHTML);
        // console.log(document.getElementById("img__"+elemId).innerHTML);
      }
      fileReader.readAsDataURL(fileToLoad);
    }
  }