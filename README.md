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
Options are documented at [https://imageoptim.com/api/post?username=xltbbqkrgs#options](https://imageoptim.com/api/post?username=xltbbqkrgs#options). Multiple options can be separated by a comma.

Example: Crop image to 100 x 100 pixels
```
[[imageOptim? &input=`[[*myImageTV]]` &options=`100x100,crop`]]
```
