# imageOptim
imageOptim extra for the MODX CMS. This extra uses the [imageOpim API](https://imageoptim.com/api) for optimizing images. This results in much smaller image file sizes than pthumb/phpthumbof or resizer.

# Usage
You can use imageOptim as a snippet or as a output filter:
```
[[imageOptim? &input=`[[*myImageTV]]` &options=`600`]]
```

```
[[*myImageTV:imageOptim=`600`]]
```


# Available options
Options are documented at [https://imageoptim.com/api/post#options](https://imageoptim.com/api/post#options). Multiple options can be separated by a comma.

Example: Crop image to 100 x 100 pixels
```
[[imageOptim? &input=`[[*myImageTV]]` &options=`100x100,crop`]]
```

# API key / username
In order to use this extra, you need to get a (currently free) api username from: https://imageoptim.com/api/username. Add the username to the `imageoptim.username` system setting.
