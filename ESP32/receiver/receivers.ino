#include <LoRa.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include "boards.h"


// WIFI credentials
const char* ssid = "ssid";
const char* password = "pass";



// HTTP server details
const String HOST_NAME = "hostname";
const String PHP_FILE_NAME = "/file";
String server;

void setup() {
  initBoard();
  delay(1500);

  Serial.println("LoRa Receiver");
  // LoRa setup
  LoRa.setPins(RADIO_CS_PIN, RADIO_RST_PIN, RADIO_DIO0_PIN);
  if (!LoRa.begin(LoRa_frequency)) {
    Serial.println("Starting LoRa failed!");
    while (1);
  }
  
  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
    Serial.println("Connecting WiFi");
    while (WiFi.status() != WL_CONNECTED) {
      delay(1000);
          Serial.print(".");
  }
  
  Serial.println("Connected to WiFi");
  Serial.println(WiFi.localIP());
}

void loop() {
  //String tempQuery = "";
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    String tempQuery = LoRa.readString();
    Serial.println(tempQuery);
    server = HOST_NAME + PHP_FILE_NAME;
    Serial.println(server);


  // Wait for another packet
    LoRa.idle();
    delay(100);
    LoRa.receive();

    
    // HTTP request
    HTTPClient http;
    WiFiClient client;
    http.begin(client, server);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    int httpCode = http.POST(tempQuery);

   
    http.end();
    
  
  }
}
