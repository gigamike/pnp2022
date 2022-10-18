#.env files
from decouple import config

AWS_DEFAULT_REGION = config('AWS_DEFAULT_REGION')
AWS_ACCESS_KEY_ID = config('AWS_ACCESS_KEY_ID')
AWS_SECRET_ACCESS_KEY = config('AWS_SECRET_ACCESS_KEY')
AWS_S3_BUCKET_NAME = config('AWS_S3_BUCKET_NAME')

print(AWS_DEFAULT_REGION)
print(AWS_ACCESS_KEY_ID)
print(AWS_SECRET_ACCESS_KEY)
print(AWS_S3_BUCKET_NAME)