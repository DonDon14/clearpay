// Stub file for mobile platforms where dart:html is not available
// This file is only used when dart:html is not available

class File {
  String name = '';
  int size = 0;
  String type = '';
}

class FileReader {
  dynamic result;
  Stream<dynamic> get onLoadEnd => const Stream.empty();
  Stream<dynamic> get onError => const Stream.empty();
  void readAsArrayBuffer(dynamic file) {}
}

class FileUploadInputElement {
  String accept = '';
  List<File>? files;
  Stream<dynamic> get onChange => const Stream.empty();
  void click() {}
}

class Blob {
  Blob(List<dynamic> data, [String? type]);
}

class Url {
  static String createObjectUrlFromBlob(Blob blob) => '';
  static void revokeObjectUrl(String url) {}
}

class ImageElement {
  String src = '';
  String crossOrigin = '';
  int naturalWidth = 0;
  Stream<dynamic> get onLoad => const Stream.empty();
}

class CanvasRenderingContext2D {
  String fillStyle = '';
  String strokeStyle = '';
  double lineWidth = 0;
  String font = '';
  String textAlign = '';
  String textBaseline = '';
  void fillRect(double x, double y, double width, double height) {}
  void strokeRect(double x, double y, double width, double height) {}
  TextMetrics measureText(String text) => TextMetrics();
  void fillText(String text, double x, double y) {}
  void drawImageScaled(dynamic image, double x, double y, double width, double height) {}
}

class TextMetrics {
  double? width = 0;
}

class CanvasElement {
  int? width;
  int? height;
  CanvasRenderingContext2D context2D = CanvasRenderingContext2D();
  CanvasElement({int? width, int? height});
  Future<Blob?> toBlob() async => null;
}

class AnchorElement {
  String href;
  AnchorElement({required this.href});
  void setAttribute(String name, String value) {}
  void click() {}
}

// Window class for mobile stub
class Window {
  void open(String url, String target) {}
}

// In dart:html, window is a top-level getter
// For the stub, we export it as a top-level getter too
Window get window => Window();


