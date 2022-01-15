// WiFi
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
ESP8266WiFiMulti wifiMulti;

// BMP280
#include <Wire.h>
#include <BME280I2C.h>
BME280I2C bmp;

// API Server
String apiServer = "localhost";

// MySQL
String mysqlHost = "host:port";
String mysqlUsername = "username";
String mysqlPassword = "password";
String mysqlDatabase = "database";
String mysqlDataTable = "table";

// NTP
#include <NTPClient.h>
#include <WiFiUdp.h>
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP);

// Data
String date;
String onlineSince;
boolean bypassHardwareCheck = false;
int deepSleepMinutes = 15;
int wrongDateCounter = 0;
String mac;

// Weather data
float temperature;
float humidity;
float pressure;

// LED
#define connectWiFiPin 14
#define uploadDataPin 12

void setup() {
  Serial.begin(115200);

  pinMode(connectWiFiPin, OUTPUT);
  pinMode(uploadDataPin, OUTPUT);

  digitalWrite(connectWiFiPin, LOW);
  digitalWrite(uploadDataPin, LOW);

  splashScreen();

  connectWiFi();
  startNTP();
  Serial.begin(115200);

  digitalWrite(connectWiFiPin, HIGH);

  if(bypassHardwareCheck == false) {
    if(checkHardware() == false) {
      Serial.println("[SETUP] BMP280 not connected");
      sleep();
    }
  }

  setWeatherData(&Serial);
  
  uploadData();

  digitalWrite(uploadDataPin, HIGH);

  delay(10);

  sleep();
}

void loop() {}

boolean checkHardware() {
  boolean hardwareOK = true;
  while (!(Serial)) {}
  Wire.begin();

  int badHardwareCounter = 0;
  
  while(!(bmp.begin())) {
    badHardwareCounter++;
    Serial.println("[BMP280] Not connected");
    hardwareOK = false;

    if(badHardwareCounter >= 5) {
      sleep();
    } else {
      delay(10);
    }
  }

  switch(bmp.chipModel()) {
    case BME280::ChipModel_BME280:
      break;
    case BME280::ChipModel_BMP280:
      hardwareOK = false;
      break;
    default:
      hardwareOK = false;
  }

  return hardwareOK;
}

void connectWiFi() {
  wakeUp();
  wifiMulti.addAP("SSID1", "PASSWORD1");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID2", "PASSWORD2");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID3", "PASSWORD3");           // ADD ACCESSPOINT

  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("##################################################");
  Serial.println("[WIFI] Connecting");
  
  while(wifiMulti.run() != WL_CONNECTED) {
    delay(100);
    Serial.print('.');
  }

  mac = String(WiFi.macAddress());
  
  Serial.println();
  Serial.print("[WIFI] Connected to: ");
  Serial.println(WiFi.SSID());
  Serial.print("[WIFI] IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.print("[WIFI] MAC Address: ");
  Serial.println(mac);
  Serial.println("##################################################");
  for(int i = 0; i < 3; i++){Serial.println();}
}

void startNTP() {
  timeClient.begin();
  // BERLIN TIME OFFSET (GMT): +2 -> 7200
  timeClient.setTimeOffset(7200);
}

void setWeatherData(Stream* client) {
  float temp(NAN), humi(NAN), pres(NAN);
  bmp.read(pres, temp, humi, BME280::TempUnit_Celsius, BME280::PresUnit_Pa);

  temperature = temp;
  humidity = humi;
  pressure = pres / 100;

  timeClient.update();
  date = timeClient.getFormattedDate();
}

void uploadData() {
  if(!(date.startsWith("1970-01-01T"))) {
    if(wrongDateCounter <= 5) {
      String request = apiServer + "/weatherstation-post-data.php";
      request += "?host=" + mysqlHost;
      request += "&username=" + mysqlUsername;
      request += "&password=" + mysqlPassword;
      request += "&database=" + mysqlDatabase;
      request += "&tablename=" + mysqlDataTable;
      request += "&temp=" + String(temperature);
      request += "&humi=" + String(humidity);
      request += "&pres=" + String(pressure);
      request += "&date=" + date;

      Serial.println(request);

      Serial.println("[MYSQL] Uploading data");

      HTTPClient http;
      http.begin(request);

      int httpCode = http.GET();

      if(httpCode > 0) {if(httpCode == HTTP_CODE_OK) {}}

      http.end();
    }
  } else {
    wrongDateCounter++;
    setWeatherData(&Serial);
    uploadData();
  }
}

void wakeUp() {
  WiFi.mode(WIFI_STA);
}

void sleep() {
  WiFi.mode(WIFI_OFF);
  Serial.println("[DEEPSLEEP] Enabled deepsleep mode");
  ESP.deepSleep(deepSleepMinutes * 60 * 1000000);
}

void splashScreen() {
  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("###################################################");
  Serial.println("###                                             ###");
  Serial.println("###   Project: IoT Weather Station              ###");
  Serial.println("###   Version: v2.5                             ###");
  Serial.println("###   Board: WeMos D1 MINI                      ###");
  Serial.println("###   Author: Jens Ostertag                     ###");
  Serial.println("###   GitHub: https://github.com/JensOstertag   ###");
  Serial.println("###                                             ###");
  Serial.println("###################################################");
  for(int i = 0; i < 3; i++){Serial.println();}
}
