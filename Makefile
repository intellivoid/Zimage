clean:
	rm -rf build

update:
	ppm --generate-package="src/Zimage"

build:
	mkdir build
	ppm --no-intro --compile="src/Zimage" --directory="build"

install:
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.zimage.ppm"