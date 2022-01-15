// WiFi
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
ESP8266WiFiMulti wifiMulti;

// API Server
String apiServer = "localhost";

// MySQL
String mysqlHost = "host:port";
String mysqlUsername = "username";
String mysqlPassword = "password";
String mysqlDatabase = "database";
String mysqlTable = "table";

// Display
#include <U8g2lib.h>
U8G2_SH1106_128X64_NONAME_F_HW_I2C u8g2(U8G2_R0, U8X8_PIN_NONE);

// LED
#define signalLED 14

// Data
boolean printingTemperature = false;
boolean printingHumidity = false;
boolean printingPressure = false;

void setup() {
  Serial.begin(115200);

  pinMode(signalLED, OUTPUT);
  
  startDisplay();

  splashScreen();

  delay(3000);

  u8g2.clearBuffer();

  printingTemperature = true;
  printingHumidity = false;
  printingPressure = false;
}

void loop() {
  if(wifiMulti.run() != WL_CONNECTED) {
    connectWiFi();
  }
  
  if(printingTemperature == true) {
    u8g2.clearBuffer();
      
    const char *ln1 = "Temperatur:";
    String val = formatData(getLatestTemperature()) + " °C";
    String date = formatDate();
     
    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t ln1Width = u8g2.getStrWidth(ln1);
    u8g2.drawUTF8((128 - ln1Width) / 2, 10, ln1);
  
    u8g2.setFont(u8g2_font_logisoso20_tf);
    u8g2_uint_t valWidth = u8g2.getStrWidth(val.c_str());
    u8g2.drawUTF8((128 - valWidth) / 2, 40, val.c_str());

    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t dtWidth = u8g2.getStrWidth(date.c_str());
    u8g2.drawUTF8((128 - dtWidth) / 2, 60, date.c_str());

    u8g2.sendBuffer();
      
    Serial.println(getLatestTemperature());
    printingTemperature = false;
    printingHumidity = true;
    printingPressure = false;
  } else if(printingHumidity == true) {
    u8g2.clearBuffer();
      
    const char *ln1 = "Luftfeuchtigkeit:";
    String val = formatData(getLatestHumidity()) + " %";
    String date = formatDate();
     
    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t ln1Width = u8g2.getStrWidth(ln1);
    u8g2.drawUTF8((128 - ln1Width) / 2, 10, ln1);

    u8g2.setFont(u8g2_font_logisoso20_tf);
    u8g2_uint_t valWidth = u8g2.getStrWidth(val.c_str());
    u8g2.drawUTF8((128 - valWidth) / 2, 40, val.c_str());

    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t dtWidth = u8g2.getStrWidth(date.c_str());
    u8g2.drawUTF8((128 - dtWidth) / 2, 60, date.c_str());

    u8g2.sendBuffer();
      
    Serial.println(getLatestHumidity());
    printingTemperature = false;
    printingHumidity = false;
    printingPressure = true;
  } else if(printingPressure == true) {
    u8g2.clearBuffer();
    
    const char *ln1 = "Luftdruck:";
    String val = formatData(getLatestPressure()) + " hPa";
    String date = formatDate();
    
    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t ln1Width = u8g2.getStrWidth(ln1);
    u8g2.drawUTF8((128 - ln1Width) / 2, 10, ln1);
    u8g2.setFont(u8g2_font_logisoso20_tf);
    u8g2_uint_t valWidth = u8g2.getStrWidth(val.c_str());
    u8g2.drawUTF8((128 - valWidth) / 2, 40, val.c_str());

    u8g2.setFont(u8g2_font_t0_11b_tf);
    u8g2_uint_t dtWidth = u8g2.getStrWidth(date.c_str());
    u8g2.drawUTF8((128 - dtWidth) / 2, 60, date.c_str());
    u8g2.sendBuffer();
    
    Serial.println(getLatestPressure());
    printingTemperature = true;
    printingHumidity = false;
    printingPressure = false;
  }
  
  delay(1);
  u8g2.clearBuffer();

  delay(5000);
}

void sendSignal(int signaltype) {
  if(signaltype == 1) {
    // Connecting to WiFi
    digitalWrite(signalLED, HIGH);
    delay(100);
    digitalWrite(signalLED, LOW);
    delay(100);
  } else if(signaltype == 2) {
    // No WiFi
    digitalWrite(signalLED, HIGH);
    delay(500);
    digitalWrite(signalLED, LOW);
    delay(500);
  } else if(signaltype == 3) {
    // Data received
    digitalWrite(signalLED, HIGH);
    delay(100);
    digitalWrite(signalLED, LOW);
  } else if(signaltype == 4) {
    // Unknown request
    digitalWrite(signalLED, HIGH);
    delay(1000);
    digitalWrite(signalLED, LOW);
  }
}

void connectWiFi() {
  wifiMulti.addAP("SSID1", "PASSWORD1");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID2", "PASSWORD2");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID3", "PASSWORD3");           // ADD ACCESSPOINT

  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("##################################################");
  Serial.println("[WIFI] Connecting");
  
  while(wifiMulti.run() != WL_CONNECTED) {
    sendSignal(1);
    Serial.print('.');
  }
  
  Serial.println();
  Serial.print("[WIFI] Connected to: ");
  Serial.println(WiFi.SSID());
  Serial.print("[WIFI] IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.println("##################################################");
  for(int i = 0; i < 3; i++){Serial.println();}
}

void startDisplay() {
  u8g2.begin();
  u8g2.clearBuffer();
}

String getLatestTemperature() {
  String request = apiServer + "/weatherstation-get-data.php";
  request += "?host=" + mysqlHost;
  request += "&username=" + mysqlUsername;
  request += "&password=" + mysqlPassword;
  request += "&database=" + mysqlDatabase;
  request += "&tablename=" + mysqlTable;
  request += "&request=latest-temp";

  HTTPClient http;
  http.begin(request);

  int httpCode = http.GET();
  String html = "00.00";

  if(httpCode > 0) {
    if(httpCode == HTTP_CODE_OK) {
      html = http.getString();
    } else {
      Serial.println(httpCode);
    }
  } else {
    Serial.println("Failed, " + httpCode);
  }
  
  http.end();

  return html;
}

String getLatestHumidity() {
  String request = apiServer + "/weatherstation-get-data.php";
  request += "?host=" + mysqlHost;
  request += "&username=" + mysqlUsername;
  request += "&password=" + mysqlPassword;
  request += "&database=" + mysqlDatabase;
  request += "&tablename=" + mysqlTable;
  request += "&request=latest-humi";

  HTTPClient http;
  http.begin(request);

  int httpCode = http.GET();
  String html = "00.00";

  if(httpCode > 0) {
    if(httpCode == HTTP_CODE_OK) {
      html = http.getString();
    } else {
      Serial.println(httpCode);
    }
  } else {
    Serial.println("Failed, " + httpCode);
  }
  
  http.end();

  return html;
}

String getLatestPressure() {
  String request = apiServer + "/weatherstation-get-data.php";
  request += "?host=" + mysqlHost;
  request += "&username=" + mysqlUsername;
  request += "&password=" + mysqlPassword;
  request += "&database=" + mysqlDatabase;
  request += "&tablename=" + mysqlTable;
  request += "&request=latest-pres";

  HTTPClient http;
  http.begin(request);

  int httpCode = http.GET();
  String html = "00.00";

  if(httpCode > 0) {
    if(httpCode == HTTP_CODE_OK) {
      html = http.getString();
    } else {
      Serial.println(httpCode);
    }
  } else {
    Serial.println("Failed, " + httpCode);
  }
  
  http.end();

  return html;
}

String getLatestUploadDate() {
  String request = apiServer + "/weatherstation-get-data.php";
  request += "?host=" + mysqlHost;
  request += "&username=" + mysqlUsername;
  request += "&password=" + mysqlPassword;
  request += "&database=" + mysqlDatabase;
  request += "&tablename=" + mysqlTable;
  request += "&request=latest-date";

  HTTPClient http;
  http.begin(request);

  int httpCode = http.GET();
  String html = "0000-00-00T00:00:00";

  if(httpCode > 0) {
    if(httpCode == HTTP_CODE_OK) {
      html = http.getString();
    } else {
      Serial.println(httpCode);
    }
  } else {
    Serial.println("Failed, " + httpCode);
  }
  
  http.end();

  return html;
}

String formatData(String value) {
  return value.substring(0, value.length() - 1);
}

String formatDate() {
  String latestUpload = getLatestUploadDate();
  String year = latestUpload.substring(0, 4);
  String month = latestUpload.substring(5, 7);
  String day = latestUpload.substring(8, 10);

  String hour = latestUpload.substring(11, 13);
  String minute = latestUpload.substring(14, 16);

  String date = day + "." + month + "." + year + " " + hour + ":" + minute;
  return date;
}

void splashScreen() {
  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("###################################################");
  Serial.println("###                                             ###");
  Serial.println("###   Project: IoT Weather Station Display      ###");
  Serial.println("###   Version: v1.2                             ###");
  Serial.println("###   Board: WeMos D1 MINI                      ###");
  Serial.println("###   Author: Jens Ostertag                     ###");
  Serial.println("###   GitHub: https://github.com/JensOstertag   ###");
  Serial.println("###                                             ###");
  Serial.println("###################################################");
  for(int i = 0; i < 3; i++){Serial.println();}

  u8g2_uint_t width;

  u8g2.setFont(u8g2_font_logisoso16_tf);
  const char *ln1 = "IoT Weather";
  width = u8g2.getStrWidth(ln1);
  u8g2.drawUTF8((128 - width) / 2, 21, ln1);

  u8g2.setFont(u8g2_font_logisoso16_tf);
  const char *ln2 = "Station";
  width = u8g2.getStrWidth(ln2);
  u8g2.drawUTF8((128 - width) / 2, 40, ln2);

  u8g2.setFont(u8g2_font_logisoso16_tf);
  const char *ln3 = "Display";
  width = u8g2.getStrWidth(ln3);
  u8g2.drawUTF8((128 - width) / 2, 59, ln3);

  u8g2.sendBuffer();
}
