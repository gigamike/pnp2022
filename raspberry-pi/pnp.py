#.env files
from decouple import config
#unique filename
import uuid
#camera
import time
import cv2
#s3
import boto3
from botocore.exceptions import NoCredentialsError
#path
from pathlib import Path

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

def upload_to_aws(local_file, bucket, s3_file):
    s3 = boto3.client('s3', aws_access_key_id=AWS_ACCESS_KEY_ID, aws_secret_access_key=AWS_SECRET_ACCESS_KEY)

    try:
        s3.upload_file(local_file, bucket, s3_file)
        print("Upload Successful")
        return True
    except FileNotFoundError:
        print("The file was not found")
        return False
    except NoCredentialsError:
        print("Credentials not available")
        return False

GPIO.setmode(GPIO.BCM)
PIR_PIN = 17
GPIO.setup(PIR_PIN, GPIO.IN)
def MOTION(PIR_PIN):
    GPIO.setup(20, GPIO.OUT)
    GPIO.output(20, 1)

    currentPath = Path().absolute()

    unique_filename = str(uuid.uuid4())

    camera_port = 0
    camera = cv2.VideoCapture(camera_port)
    time.sleep(0.1)  # If you don't wait, the image will be dark
    return_value, image = camera.read()

    localFile = str(currentPath) + "/img/" + config('RASPBERRY_PI_ID') + "-" + unique_filename + ".jpg"
    s3File = config('RASPBERRY_PI_ID') + "-" + unique_filename + ".jpg"

    print(localFile)
    print(s3File)

    cv2.imwrite(localFile, image)
    del(camera)  # so that others can use the camera as soon as possible

    uploaded = upload_to_aws(localFile, AWS_S3_BUCKET_NAME, s3File)

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