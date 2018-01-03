# processor-skip-lines

[![Build Status](https://travis-ci.org/keboola/processor-skip-lines.svg?branch=master)](https://travis-ci.org/keboola/processor-skip-lines)

Removes a given number of lines from each file in `/data/in/files` and `/data/in/tables` (including sliced tables). Copies manifest files.

This processor uses Linux `tail` command, so it does not correctly interpret multiline CSV rows. 
 
## Development
 
Clone this repository and init the workspace with following commands:

- `docker-compose build`

### TDD 

 - Edit the code
 - Run `docker-compose run --rm tests` 
 - Repeat
 
# Integration
 - Build is started after push on [Travis CI](https://travis-ci.org/keboola/processor-skip-lines)
 - [Build steps](https://github.com/keboola/processor-skip-lines/blob/master/.travis.yml)
   - build image
   - execute tests against new image
   - publish image to AWS ECR if the release is tagged
   
# Usage

## Sample configuration

```
{  
    "definition": {
        "component": "keboola.processor-skip-lines"
    },
    "parameters": {
        "lines": 1
    }
}
```

## Parameters

### lines

Number of lines to remove from each file.
