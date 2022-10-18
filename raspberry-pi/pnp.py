#.env files
from decouple import config

# PIR sensor
import RPi.GPIO as GPIO
import time
import urllib.request, json

AWS_DEFAULT_REGION = config('AWS_DEFAULT_REGION')
AWS_ACCESS_KEY_ID = config('AWS_ACCESS_KEY_ID')
AWS_SECRET_ACCESS_KEY = config('AWS_SECRET_ACCESS_KEY')
AWS_S3_BUCKET_NAME = config('AWS_S3_BUCKET_NAME')

print(AWS_DEFAULT_REGION)
print(AWS_ACCESS_KEY_ID)
print(AWS_SECRET_ACCESS_KEY)
print(AWS_S3_BUCKET_NAME)

GPIO.setmode(GPIO.BCM)
PIR_PIN = 17
GPIO.setup(PIR_PIN, GPIO.IN)
def MOTION(PIR_PIN):
    GPIO.setup(20, GPIO.OUT)
    GPIO.output(20, 1)
    
    print("Motion Detected!")
    print ("PIR Module Test (CTRL+C to exit)")
    time.sleep(2)
    print ("Ready")
    GPIO.output(20, 0)
try:
    GPIO.add_event_detect(PIR_PIN, GPIO.RISING, callback=MOTION)
    while 1:
        time.sleep(100)
except KeyboardInterrupt:
    print (" Quit")
    GPIO.cleanup()